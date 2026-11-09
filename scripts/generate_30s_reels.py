"""
TakvimTakip 30-Second Full HD (1080x1920) Vertical Reels Generator
Without voiceover, featuring real in-app screenshots & energetic background music.
Ultra-optimized with imageio.get_writer for zero memory buildup.
"""

import os
import math
import subprocess
import time
import wave
import gc
import numpy as np
from PIL import Image, ImageDraw, ImageFont
import imageio
import imageio_ffmpeg

# Paths
BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
ASSETS_DIR = os.path.join(BASE_DIR, 'public', 'reels_assets', 'takvimtakip')
OUTPUT_DIR = os.path.join(BASE_DIR, 'public')
AUDIO_PATH = os.path.join(OUTPUT_DIR, 'reels_audio_30s.wav')
TEMP_VIDEO_PATH = os.path.join(OUTPUT_DIR, 'temp_video_30s.mp4')
FINAL_VIDEO_PATH = os.path.join(OUTPUT_DIR, 'TakvimTakip_30s_Reels.mp4')

# Video Constants
WIDTH = 1080
HEIGHT = 1920
FPS = 30
DURATION = 30.0
TOTAL_FRAMES = int(DURATION * FPS) # 900 frames

# Load System Fonts
FONT_BOLD_PATH = r"C:\Windows\Fonts\segoeuib.ttf"
FONT_REG_PATH = r"C:\Windows\Fonts\segoeui.ttf"

def get_font(size, bold=True):
    path = FONT_BOLD_PATH if bold else FONT_REG_PATH
    try:
        return ImageFont.truetype(path, size)
    except Exception:
        return ImageFont.load_default()

font_title_lg = get_font(48, bold=True)
font_title_md = get_font(38, bold=True)
font_title_sm = get_font(32, bold=True)
font_badge = get_font(26, bold=True)
font_body = get_font(26, bold=False)
font_body_bold = get_font(26, bold=True)
font_sub = get_font(22, bold=False)

# Phone Layout
phone_w = 680
phone_h = 1380
phone_x = (WIDTH - phone_w) // 2
phone_y = 440
screen_x = phone_x + 14
screen_y = phone_y + 14
screen_w = phone_w - 28
screen_h = phone_h - 28

def load_and_scale(filename, target_w=None, target_h=None):
    path = os.path.join(ASSETS_DIR, filename)
    if not os.path.exists(path):
        print(f"Warning: not found {path}")
        return None
    with Image.open(path) as img:
        img = img.convert('RGBA')
        if target_w and target_h:
            return img.resize((target_w, target_h), Image.Resampling.LANCZOS)
        elif target_w:
            h = int(img.height * (target_w / img.width))
            return img.resize((target_w, h), Image.Resampling.LANCZOS)
        elif target_h:
            w = int(img.width * (target_h / img.height))
            return img.resize((w, target_h), Image.Resampling.LANCZOS)
        return img

print("Pre-scaling all screenshots and assets...")
scaled_logo_top = load_and_scale('logo222.png', target_h=50)
scaled_logo_cta = load_and_scale('logo222.png', target_w=int(screen_w * 0.75))
scaled_push = load_and_scale('WhatsApp Image 2026-11-09 at 20.48.29.jpeg', target_w=screen_w - 24)
scaled_wa = load_and_scale('WhatsApp Image 2026-11-09 at 20.51.38.jpeg', target_w=screen_w - 20)
scaled_dash = load_and_scale('WhatsApp Image 2026-11-09 at 20.52.22 (5).jpeg', target_w=screen_w)
scaled_cal = load_and_scale('WhatsApp Image 2026-11-09 at 20.52.22 (3).jpeg', target_w=screen_w)
scaled_web_top = load_and_scale('WhatsApp Image 2026-11-09 at 20.52.22 (2).jpeg', target_w=screen_w)
scaled_web_bot = load_and_scale('WhatsApp Image 2026-11-09 at 20.52.22 (1).jpeg', target_w=screen_w)
scaled_modal = load_and_scale('WhatsApp Image 2026-11-09 at 20.52.22.jpeg', target_w=screen_w)
print("All assets pre-scaled and cached in memory.")

# Pre-render static background gradient
base_bg = Image.new('RGB', (WIDTH, HEIGHT), (11, 15, 25))
draw_bg = ImageDraw.Draw(base_bg)
for y in range(0, HEIGHT, 10):
    factor = y / HEIGHT
    r = int(11 * (1 - factor) + 22 * factor)
    g = int(15 * (1 - factor) + 30 * factor)
    b = int(25 * (1 - factor) + 46 * factor)
    draw_bg.rectangle((0, y, WIDTH, y+10), fill=(r, g, b))

# --- STEP 1: AUDIO TRACK GENERATION ---
def generate_audio():
    if os.path.exists(AUDIO_PATH) and os.path.getsize(AUDIO_PATH) > 1000000:
        print("Using existing audio track:", AUDIO_PATH)
        return

    print("Generating 30s upbeat rhythmic audio track with SFX...")
    sr = 44100
    total_samples = int(sr * DURATION)
    audio = np.zeros((total_samples, 2), dtype=np.float32)

    bpm = 124.0
    beat_dur = 60.0 / bpm
    total_beats = int(DURATION / beat_dur)

    # 1. Kicks
    for b in range(total_beats):
        t_start = b * beat_dur
        idx_start = int(t_start * sr)
        kick_len = int(0.22 * sr)
        if idx_start + kick_len > total_samples:
            kick_len = total_samples - idx_start
        if kick_len <= 0:
            continue
        kt = np.linspace(0, 0.22, kick_len, endpoint=False)
        freq = 45.0 + 105.0 * np.exp(-kt * 28.0)
        phase = 2 * np.pi * np.cumsum(freq) / sr
        env = np.exp(-kt * 18.0)
        kick = np.sin(phase) * env * 0.75
        audio[idx_start:idx_start+kick_len, 0] += kick
        audio[idx_start:idx_start+kick_len, 1] += kick

    # 2. Snares
    for b in range(1, total_beats, 2):
        t_start = b * beat_dur
        idx_start = int(t_start * sr)
        snare_len = int(0.18 * sr)
        if idx_start + snare_len > total_samples:
            snare_len = total_samples - idx_start
        if snare_len <= 0:
            continue
        st = np.linspace(0, 0.18, snare_len, endpoint=False)
        noise = (np.random.rand(snare_len) * 2 - 1) * np.exp(-st * 24.0) * 0.42
        tone = np.sin(2 * np.pi * 185 * st) * np.exp(-st * 30.0) * 0.32
        snare = noise + tone
        audio[idx_start:idx_start+snare_len, 0] += snare
        audio[idx_start:idx_start+snare_len, 1] += snare

    # 3. Hi-hats
    for step in range(total_beats * 4):
        t_start = step * (beat_dur / 4.0)
        idx_start = int(t_start * sr)
        hat_len = int(0.045 * sr)
        if idx_start + hat_len > total_samples:
            hat_len = total_samples - idx_start
        if hat_len <= 0:
            continue
        ht = np.linspace(0, 0.045, hat_len, endpoint=False)
        vol = 0.20 if (step % 2 == 1) else 0.12
        hat = (np.random.rand(hat_len) * 2 - 1) * np.exp(-ht * 80.0) * vol
        audio[idx_start:idx_start+hat_len, 0] += hat * 0.8
        audio[idx_start:idx_start+hat_len, 1] += hat * 1.0

    # 4. Bassline
    chords = [220.0 / 2, 174.61 / 2, 130.81 / 2, 196.0 / 2]
    for b in range(total_beats):
        chord_idx = (b // 4) % len(chords)
        base_f = chords[chord_idx]
        t_start = b * beat_dur
        idx_start = int(t_start * sr)
        bass_len = int(0.35 * sr)
        if idx_start + bass_len > total_samples:
            bass_len = total_samples - idx_start
        if bass_len <= 0:
            continue
        bt = np.linspace(0, 0.35, bass_len, endpoint=False)
        env = np.exp(-bt * 6.5)
        bass = (np.sin(2 * np.pi * base_f * bt) + 0.35 * np.sin(2 * np.pi * base_f * 2 * bt)) * env * 0.32
        audio[idx_start:idx_start+bass_len, 0] += bass
        audio[idx_start:idx_start+bass_len, 1] += bass

    # 5. Transition Whooshes
    for ts in [3.6, 7.6, 11.6, 16.6, 20.6, 24.6]:
        idx_start = int(ts * sr)
        w_len = int(0.40 * sr)
        wt = np.linspace(0, 0.40, w_len, endpoint=False)
        noise = (np.random.rand(w_len) * 2 - 1) * (wt / 0.40) * 0.28
        sweep_f = 200.0 + 1200.0 * (wt / 0.40)**2
        tone = np.sin(2 * np.pi * sweep_f * wt) * (wt / 0.40) * 0.18
        audio[idx_start:idx_start+w_len, 0] += (noise + tone)
        audio[idx_start:idx_start+w_len, 1] += (noise + tone)

    # 6. Notification Ding (8.3s)
    ding_idx = int(8.25 * sr)
    ding_len = int(0.6 * sr)
    dt = np.linspace(0, 0.6, ding_len, endpoint=False)
    ding = (np.sin(2 * np.pi * 987.77 * dt) + 0.5 * np.sin(2 * np.pi * 1318.51 * dt)) * np.exp(-dt * 6.0) * 0.45
    audio[ding_idx:ding_idx+ding_len, 0] += ding
    audio[ding_idx:ding_idx+ding_len, 1] += ding

    # 7. WhatsApp Pop (21.4s)
    pop_idx = int(21.35 * sr)
    pop_len = int(0.25 * sr)
    pt = np.linspace(0, 0.25, pop_len, endpoint=False)
    pop = np.sin(2 * np.pi * (450 + 200 * np.exp(-pt * 30)) * pt) * np.exp(-pt * 25.0) * 0.5
    audio[pop_idx:pop_idx+pop_len, 0] += pop
    audio[pop_idx:pop_idx+pop_len, 1] += pop

    # 8. Chime (25.2s)
    chime_idx = int(25.15 * sr)
    chime_len = int(1.2 * sr)
    ct = np.linspace(0, 1.2, chime_len, endpoint=False)
    chime = (np.sin(2 * np.pi * 523.25 * ct) + np.sin(2 * np.pi * 659.25 * ct) + np.sin(2 * np.pi * 783.99 * ct)) * np.exp(-ct * 3.5) * 0.35
    audio[chime_idx:chime_idx+chime_len, 0] += chime
    audio[chime_idx:chime_idx+chime_len, 1] += chime

    # Fade out
    fade_start = int(28.8 * sr)
    fade_len = total_samples - fade_start
    if fade_len > 0:
        fade_curve = np.linspace(1.0, 0.0, fade_len)
        audio[fade_start:, 0] *= fade_curve
        audio[fade_start:, 1] *= fade_curve

    max_val = np.max(np.abs(audio))
    if max_val > 0.95:
        audio = audio * (0.95 / max_val)

    audio_int16 = (audio * 32767).astype(np.int16)
    with wave.open(AUDIO_PATH, 'wb') as wav:
        wav.setnchannels(2)
        wav.setsampwidth(2)
        wav.setframerate(sr)
        wav.writeframes(audio_int16.tobytes())
    print("Audio written:", AUDIO_PATH)

# --- STEP 2: FRAME DRAWING HELPERS ---
def draw_rounded_rect(draw, bbox, radius, fill=None, outline=None, width=1):
    draw.rounded_rectangle(bbox, radius=radius, fill=fill, outline=outline, width=width)

def draw_subtitles(draw, scene):
    if scene == 1:
        draw_rounded_rect(draw, (80, 150, 1000, 220), radius=20, fill=(254, 212, 82), outline=(255, 255, 255), width=2)
        draw.text((100, 162), "🏡 VİLLA VEYA BUNGALOVUNUZ VARSA", font=font_badge, fill=(15, 23, 42))

        draw_rounded_rect(draw, (80, 235, 1000, 325), radius=20, fill=(15, 23, 42, 240), outline=(254, 212, 82), width=3)
        draw.text((105, 252), "BU VİDEOYU MUTLAKA KAYDEDİN!", font=font_title_md, fill=(255, 255, 255))

        draw_rounded_rect(draw, (180, 340, 900, 400), radius=16, fill=(79, 70, 229, 230))
        draw.text((210, 352), "✨ Rezervasyon Karmaşasına Tek Dokunuşla Son!", font=font_body_bold, fill=(255, 255, 255))

    elif scene == 2:
        draw_rounded_rect(draw, (120, 150, 960, 220), radius=20, fill=(16, 185, 129), outline=(255, 255, 255), width=2)
        draw.text((170, 162), "📋 BUGÜN GİRİŞ / ÇIKIŞ TAKİBİ", font=font_badge, fill=(15, 23, 42))

        draw_rounded_rect(draw, (80, 235, 1000, 325), radius=20, fill=(15, 23, 42, 240), outline=(16, 185, 129), width=3)
        draw.text((115, 252), "BUGÜN KİM GELİYOR, KİM ÇIKIYOR?", font=font_title_md, fill=(255, 255, 255))

        draw_rounded_rect(draw, (220, 340, 860, 400), radius=16, fill=(16, 185, 129, 230))
        draw.text((250, 352), "⚡ Tek Ekranda Canlı Takip Edin, Karışıklığı Önleyin", font=font_body_bold, fill=(255, 255, 255))

    elif scene == 3:
        draw_rounded_rect(draw, (120, 150, 960, 220), radius=20, fill=(245, 158, 11), outline=(255, 255, 255), width=2)
        draw.text((160, 162), "🔔 ERTESİ GÜN ÇIKIŞ BİLDİRİMİ!", font=font_badge, fill=(15, 23, 42))

        draw_rounded_rect(draw, (80, 235, 1000, 325), radius=20, fill=(15, 23, 42, 240), outline=(245, 158, 11), width=3)
        draw.text((95, 252), "TEMİZLİK & HAZIRLIĞI ASLA KAÇIRMAYIN", font=font_title_sm, fill=(255, 255, 255))

        draw_rounded_rect(draw, (200, 340, 880, 400), radius=16, fill=(245, 158, 11, 230))
        draw.text((230, 352), "📲 Telefonunuza Otomatik Hatırlatma Bildirimi Gelir", font=font_body_bold, fill=(255, 255, 255))

    elif scene == 4:
        draw_rounded_rect(draw, (100, 150, 980, 220), radius=20, fill=(79, 70, 229), outline=(255, 255, 255), width=2)
        draw.text((135, 162), "🔄 OTOMATİK iCAL SENKRONİZASYONU", font=font_badge, fill=(255, 255, 255))

        draw_rounded_rect(draw, (80, 235, 1000, 325), radius=20, fill=(15, 23, 42, 240), outline=(79, 70, 229), width=3)
        draw.text((120, 252), "AIRBNB • BOOKING • ACENTELER", font=font_title_md, fill=(255, 255, 255))

        draw_rounded_rect(draw, (180, 340, 900, 400), radius=16, fill=(79, 70, 229, 230))
        draw.text((215, 352), "🛡️ Bir Yerden Tutulunca Diğer Tüm Takvimler Kapansın!", font=font_body_bold, fill=(255, 255, 255))

    elif scene == 5:
        draw_rounded_rect(draw, (90, 150, 990, 220), radius=20, fill=(99, 102, 241), outline=(255, 255, 255), width=2)
        draw.text((125, 162), "🌐 VİLLANIZA ÖZEL WEB TANITIM LİNKİ!", font=font_badge, fill=(255, 255, 255))

        draw_rounded_rect(draw, (80, 235, 1000, 325), radius=20, fill=(15, 23, 42, 240), outline=(99, 102, 241), width=3)
        draw.text((95, 252), "FOTOĞRAF • FİYAT • KONUM • TAKVİM", font=font_title_sm, fill=(255, 255, 255))

        draw_rounded_rect(draw, (190, 340, 890, 400), radius=16, fill=(99, 102, 241, 230))
        draw.text((220, 352), "✨ Müşterinize Tek Link Gönderin, Tüm Bilgileri Görsün", font=font_body_bold, fill=(255, 255, 255))

    elif scene == 6:
        draw_rounded_rect(draw, (100, 150, 980, 220), radius=20, fill=(37, 211, 102), outline=(255, 255, 255), width=2)
        draw.text((140, 162), "💬 TEK TIKLA WHATSAPP'TAN İLETİN!", font=font_badge, fill=(15, 23, 42))

        draw_rounded_rect(draw, (80, 235, 1000, 325), radius=20, fill=(15, 23, 42, 240), outline=(37, 211, 102), width=3)
        draw.text((110, 252), "REZERVASYON VE ÖDEME BİLGİLERİ", font=font_title_md, fill=(255, 255, 255))

        draw_rounded_rect(draw, (180, 340, 900, 400), radius=16, fill=(37, 211, 102, 230))
        draw.text((215, 352), "🚀 Hazır Bilgi Fişi Anında Misafirin Telefonunda", font=font_body_bold, fill=(15, 23, 42))

    elif scene == 7:
        draw_rounded_rect(draw, (120, 150, 960, 220), radius=20, fill=(254, 212, 82), outline=(255, 255, 255), width=2)
        draw.text((170, 162), "🎁 14 GÜN ÜCRETSİZ DENEYİN!", font=font_badge, fill=(15, 23, 42))

        draw_rounded_rect(draw, (80, 235, 1000, 325), radius=20, fill=(15, 23, 42, 240), outline=(254, 212, 82), width=3)
        draw.text((120, 252), "APP STORE & GOOGLE PLAY'DE!", font=font_title_md, fill=(255, 255, 255))

        draw_rounded_rect(draw, (200, 340, 880, 400), radius=16, fill=(79, 70, 229, 230))
        draw.text((245, 352), "🔗 Link Profilde & takvimtakip.com", font=font_body_bold, fill=(255, 255, 255))

def render_frame_fast(t):
    img = base_bg.copy()
    draw = ImageDraw.Draw(img)

    # Ambient moving glow orbs
    orb1_y = int(300 + 80 * math.sin(t * 1.5))
    orb2_y = int(1400 + 100 * math.cos(t * 1.2))
    draw.ellipse((WIDTH//2 - 400, orb1_y - 200, WIDTH//2 + 400, orb1_y + 400), fill=(25, 35, 75))
    draw.ellipse((WIDTH//2 - 350, orb2_y - 250, WIDTH//2 + 350, orb2_y + 350), fill=(20, 45, 60))

    # Determine Scene
    if t < 4.0:
        scene = 1
        st = t
    elif t < 8.0:
        scene = 2
        st = t - 4.0
    elif t < 12.0:
        scene = 3
        st = t - 8.0
    elif t < 17.0:
        scene = 4
        st = t - 12.0
    elif t < 21.0:
        scene = 5
        st = t - 17.0
    elif t < 25.0:
        scene = 6
        st = t - 21.0
    else:
        scene = 7
        st = t - 25.0

    # Draw Subtitles
    draw_subtitles(draw, scene)

    # Top Brand Bar
    draw_rounded_rect(draw, (60, 50, WIDTH - 60, 130), radius=28, fill=(15, 23, 42, 230), outline=(51, 65, 85, 200), width=2)
    if scaled_logo_top:
        img.paste(scaled_logo_top, (90, 65), scaled_logo_top)
        draw.text((100 + scaled_logo_top.width, 74), "• Villa & Bungalov Takvim Yönetimi", font=font_body_bold, fill=(226, 232, 240))

    # Phone Outer Frame
    draw_rounded_rect(draw, (phone_x - 12, phone_y - 12, phone_x + phone_w + 12, phone_y + phone_h + 16), radius=56, fill=(0, 0, 0))
    draw_rounded_rect(draw, (phone_x, phone_y, phone_x + phone_w, phone_y + phone_h), radius=50, fill=(30, 41, 59), outline=(100, 116, 139), width=4)

    # Inner Screen
    draw_rounded_rect(draw, (screen_x, screen_y, screen_x + screen_w, screen_y + screen_h), radius=42, fill=(248, 250, 252))

    screen_img = Image.new('RGBA', (screen_w, screen_h), (248, 250, 252, 255))
    s_draw = ImageDraw.Draw(screen_img)

    # Scene Content
    if scene == 1:
        if scaled_dash:
            screen_img.paste(scaled_dash, (0, 40), scaled_dash)
        pulse = 0.5 + 0.5 * math.sin(st * 6)
        s_draw.rounded_rectangle((30, 180, screen_w - 30, 360), radius=20, outline=(79, 70, 229, int(150 + 105 * pulse)), width=4)

    elif scene == 2:
        if scaled_dash:
            screen_img.paste(scaled_dash, (0, 30), scaled_dash)
        pulse = 0.5 + 0.5 * math.sin(st * 7)
        s_draw.rounded_rectangle((24, 175, screen_w - 24, 385), radius=22, outline=(16, 185, 129, int(160 + 95 * pulse)), width=5)
        s_draw.rounded_rectangle((screen_w - 260, 125, screen_w - 30, 175), radius=14, fill=(16, 185, 129), outline=(255, 255, 255), width=2)
        s_draw.text((screen_w - 245, 137), "✓ 1 Misafir Geliyor", font=font_sub, fill=(255, 255, 255))

    elif scene == 3:
        if scaled_cal:
            screen_img.paste(scaled_cal, (0, 0), scaled_cal)
        slide_prog = min(1.0, st / 0.6)
        y_pos = int(-140 + (190 * (1 - (1 - slide_prog)**3)))
        if scaled_push:
            ph = scaled_push.height
            s_draw.rounded_rectangle((10, y_pos + 6, screen_w - 10, y_pos + ph + 10), radius=24, fill=(0, 0, 0, 120))
            screen_img.paste(scaled_push, (12, y_pos), scaled_push)
            pulse = 0.5 + 0.5 * math.sin(st * 8)
            s_draw.rounded_rectangle((12, y_pos, screen_w - 12, y_pos + ph), radius=24, outline=(245, 158, 11, int(160 + 95 * pulse)), width=3)

    elif scene == 4:
        if scaled_cal:
            screen_img.paste(scaled_cal, (0, 0), scaled_cal)
        s_draw.rounded_rectangle((15, screen_h - 480, screen_w - 15, screen_h - 40), radius=28, fill=(15, 23, 42, 235), outline=(79, 70, 229), width=3)
        s_draw.text((45, screen_h - 455), "🔄 Çift Yönlü iCal Entegrasyonu", font=font_body_bold, fill=(254, 212, 82))

        s_draw.rounded_rectangle((40, screen_h - 395, 290, screen_h - 280), radius=18, fill=(255, 56, 92), outline=(255, 255, 255), width=2)
        s_draw.text((65, screen_h - 365), "airbnb", font=font_badge, fill=(255, 255, 255))
        s_draw.text((65, screen_h - 325), "Otomatik İçe/Dışa", font=font_sub, fill=(255, 240, 240))

        s_draw.rounded_rectangle((screen_w - 290, screen_h - 395, screen_w - 40, screen_h - 280), radius=18, fill=(0, 53, 128), outline=(255, 255, 255), width=2)
        s_draw.text((screen_w - 270, screen_h - 365), "Booking.com", font=font_badge, fill=(255, 255, 255))
        s_draw.text((screen_w - 270, screen_h - 325), "Anlık Senkronize", font=font_sub, fill=(240, 245, 255))

        s_draw.rounded_rectangle((screen_w//2 - 60, screen_h - 375, screen_w//2 + 60, screen_h - 300), radius=20, fill=(79, 70, 229), outline=(255, 255, 255), width=2)
        s_draw.text((screen_w//2 - 20, screen_h - 355), "⇄", font=font_title_md, fill=(255, 255, 255))

        s_draw.rounded_rectangle((40, screen_h - 250, screen_w - 40, screen_h - 70), radius=16, fill=(22, 101, 52))
        s_draw.text((70, screen_h - 225), "✓ Çifte Rezervasyon Riski %100 Ortadan Kalktı", font=font_body_bold, fill=(255, 255, 255))
        s_draw.text((70, screen_h - 185), "Bir takvim dolduğunda tüm kanallar anında kapanır.", font=font_sub, fill=(220, 252, 231))

    elif scene == 5:
        scroll_prog = min(1.0, max(0.0, (st - 0.5) / 3.0))
        if scaled_web_top and scaled_web_bot:
            th = scaled_web_top.height
            scroll_offset = int(scroll_prog * (th - 250))
            # Crop to fit inside screen_h rather than pasting with large negative offsets
            crop_top_y = min(scroll_offset, th)
            crop_h = min(screen_h, th - crop_top_y)
            if crop_h > 0:
                cropped_top = scaled_web_top.crop((0, crop_top_y, screen_w, crop_top_y + crop_h))
                screen_img.paste(cropped_top, (0, 0), cropped_top)

            if crop_h < screen_h:
                bot_y_start = crop_h
                bot_crop_h = min(screen_h - bot_y_start, scaled_web_bot.height)
                if bot_crop_h > 0:
                    cropped_bot = scaled_web_bot.crop((0, 0, screen_w, bot_crop_h))
                    screen_img.paste(cropped_bot, (0, bot_y_start), cropped_bot)

        s_draw.rounded_rectangle((25, screen_h - 200, screen_w - 25, screen_h - 30), radius=22, fill=(15, 23, 42, 235), outline=(99, 102, 241), width=3)
        s_draw.text((45, screen_h - 180), "📸 Fotoğraflar  💰 Fiyatlar  📍 Kaş/Kalkan", font=font_body_bold, fill=(255, 255, 255))
        s_draw.text((45, screen_h - 130), "📅 Güncel Takvim & WhatsApp Tek Sayfada!", font=font_body_bold, fill=(254, 212, 82))

    elif scene == 6:
        if st < 1.4:
            if scaled_modal:
                screen_img.paste(scaled_modal, (0, 0), scaled_modal)
            tap_pulse = 0.5 + 0.5 * math.sin(st * 10)
            s_draw.rounded_rectangle((40, screen_h - 350, screen_w - 40, screen_h - 260), radius=22, outline=(79, 70, 229, int(150 + 105 * tap_pulse)), width=6)
            s_draw.text((screen_w//2 - 110, screen_h - 325), "👉 Dokunun...", font=font_badge, fill=(79, 70, 229))
        else:
            s_draw.rectangle((0, 0, screen_w, screen_h), fill=(234, 238, 233))
            s_draw.rectangle((0, 0, screen_w, 100), fill=(7, 94, 84))
            s_draw.text((60, 35), "💬 WhatsApp • Deneme Misafir", font=font_body_bold, fill=(255, 255, 255))

            if scaled_wa:
                wah = scaled_wa.height
                y_wa = int(140)
                screen_img.paste(scaled_wa, (10, y_wa), scaled_wa)
                s_draw.rounded_rectangle((10, y_wa, screen_w - 10, y_wa + wah), radius=20, outline=(37, 211, 102), width=3)

                s_draw.rounded_rectangle((25, screen_h - 220, screen_w - 25, screen_h - 40), radius=22, fill=(15, 23, 42, 235), outline=(37, 211, 102), width=3)
                s_draw.text((45, screen_h - 195), "✓ Giriş, Çıkış, Depozito & Fiyat Detayları", font=font_body_bold, fill=(255, 255, 255))
                s_draw.text((45, screen_h - 145), "Müşterinize tek dokunuşla hazır iletildi!", font=font_body_bold, fill=(37, 211, 102))

    elif scene == 7:
        s_draw.rectangle((0, 0, screen_w, screen_h), fill=(15, 23, 42))
        if scaled_logo_cta:
            lw = scaled_logo_cta.width
            screen_img.paste(scaled_logo_cta, ((screen_w - lw)//2, 120), scaled_logo_cta)

        s_draw.rounded_rectangle((30, 280, screen_w - 30, 430), radius=24, fill=(254, 212, 82), outline=(255, 255, 255), width=3)
        s_draw.text((65, 310), "🎁 14 GÜN ÜCRETSİZ DENEYİN", font=font_title_sm, fill=(15, 23, 42))
        s_draw.text((95, 370), "Kredi kartı gerekmez • Anında başlayın", font=font_body_bold, fill=(30, 41, 59))

        s_draw.rounded_rectangle((50, 480, screen_w - 50, 580), radius=20, fill=(30, 41, 59), outline=(100, 116, 139), width=2)
        s_draw.text((80, 505), "🍏 App Store'dan İndirin", font=font_badge, fill=(255, 255, 255))
        s_draw.text((80, 545), "iPhone ve iPad ile %100 Uyumlu", font=font_sub, fill=(148, 163, 184))

        s_draw.rounded_rectangle((50, 610, screen_w - 50, 710), radius=20, fill=(30, 41, 59), outline=(100, 116, 139), width=2)
        s_draw.text((80, 635), "▶ Google Play'den İndirin", font=font_badge, fill=(255, 255, 255))
        s_draw.text((80, 675), "Tüm Android Cihazlarda Yayında", font=font_sub, fill=(148, 163, 184))

        btn_pulse = 0.5 + 0.5 * math.sin(st * 7)
        s_draw.rounded_rectangle((40, 780, screen_w - 40, 890), radius=26, fill=(79, 70, 229), outline=(254, 212, 82, int(180 + 75 * btn_pulse)), width=4)
        s_draw.text((screen_w//2 - 160, 815), "Hemen Başlayın ➔", font=font_title_sm, fill=(255, 255, 255))

        s_draw.text((screen_w//2 - 130, 930), "takvimtakip.com", font=font_body_bold, fill=(254, 212, 82))
        s_draw.text((screen_w//2 - 80, 980), "Link Profilde!", font=font_sub, fill=(148, 163, 184))

    notch_w = 170
    notch_h = 36
    s_draw.rounded_rectangle(((screen_w - notch_w)//2, 14, (screen_w + notch_w)//2, 14 + notch_h), radius=18, fill=(0, 0, 0))

    img.paste(screen_img, (screen_x, screen_y), screen_img)

    # Bottom Progress Bar
    progress_w = int((t / DURATION) * (WIDTH - 120))
    draw.rounded_rectangle((60, HEIGHT - 70, WIDTH - 60, HEIGHT - 55), radius=8, fill=(30, 41, 59))
    draw.rounded_rectangle((60, HEIGHT - 70, 60 + progress_w, HEIGHT - 55), radius=8, fill=(254, 212, 82))

    draw.text((60, HEIGHT - 45), f"00:{int(t):02d} / 00:30", font=font_sub, fill=(148, 163, 184))
    draw.text((WIDTH - 240, HEIGHT - 45), "TakvimTakip Reels", font=font_sub, fill=(148, 163, 184))

    return img

def render_video():
    generate_audio()

    print(f"Starting optimized video frame rendering ({TOTAL_FRAMES} frames at {FPS} FPS)...")
    writer = imageio.get_writer(
        TEMP_VIDEO_PATH,
        fps=FPS,
        codec='libx264',
        macro_block_size=None,
        ffmpeg_params=['-preset', 'fast', '-crf', '20', '-pix_fmt', 'yuv420p']
    )

    start_time = time.time()
    for frame_idx in range(TOTAL_FRAMES):
        t = frame_idx / float(FPS)
        frame_img = render_frame_fast(t)
        frame_arr = np.asarray(frame_img)
        writer.append_data(frame_arr)

        if (frame_idx + 1) % 150 == 0 or frame_idx == TOTAL_FRAMES - 1:
            elapsed = time.time() - start_time
            fps_render = (frame_idx + 1) / elapsed
            print(f"Rendered {frame_idx + 1}/{TOTAL_FRAMES} frames ({((frame_idx + 1)/TOTAL_FRAMES)*100:.1f}%) - {fps_render:.1f} FPS", flush=True)

    writer.close()
    render_time = time.time() - start_time
    print(f"Video track rendered in {render_time:.1f}s. Merging with background audio...")

    # Merge audio & video with ffmpeg
    ffmpeg_exe = imageio_ffmpeg.get_ffmpeg_exe()
    cmd = [
        ffmpeg_exe,
        '-y',
        '-i', TEMP_VIDEO_PATH,
        '-i', AUDIO_PATH,
        '-c:v', 'copy',
        '-c:a', 'aac',
        '-b:a', '192k',
        '-shortest',
        FINAL_VIDEO_PATH
    ]
    subprocess.run(cmd, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, check=True)

    if os.path.exists(TEMP_VIDEO_PATH):
        try:
            os.remove(TEMP_VIDEO_PATH)
        except Exception:
            pass

    print(f"SUCCESS! 30-second Full HD Reels Video created: {FINAL_VIDEO_PATH}")
    print(f"File Size: {os.path.getsize(FINAL_VIDEO_PATH) / (1024*1024):.2f} MB")

if __name__ == '__main__':
    render_video()
