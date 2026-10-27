-- Schema for VillaTakvim SaaS Platform

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    phone TEXT NOT NULL,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'owner', -- 'admin', 'owner'
    status TEXT NOT NULL DEFAULT 'active', -- 'active', 'passive'
    subscription_plan TEXT NOT NULL DEFAULT 'basic', -- 'free', 'basic', 'pro', 'enterprise'
    max_villas INTEGER NOT NULL DEFAULT 5,
    license_expires_at TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS villas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    slug TEXT NOT NULL UNIQUE,
    short_description TEXT,
    detailed_description TEXT,
    city TEXT NOT NULL,
    district TEXT NOT NULL,
    address TEXT,
    google_maps_url TEXT,
    max_guests INTEGER NOT NULL DEFAULT 4,
    bedrooms INTEGER NOT NULL DEFAULT 2,
    bathrooms INTEGER NOT NULL DEFAULT 1,
    features TEXT, -- JSON string of features e.g. ["havuz", "jakuzi", "wifi"]
    min_stay_nights INTEGER NOT NULL DEFAULT 1,
    checkin_time TEXT DEFAULT '15:00',
    checkout_time TEXT DEFAULT '10:00',
    deposit_amount TEXT,
    pool_heating_fee TEXT,
    cleaning_days INTEGER DEFAULT 0,
    cleaning_fee TEXT,
    google_drive_link TEXT,
    property_type TEXT DEFAULT 'Villa',
    status TEXT NOT NULL DEFAULT 'active', -- 'active', 'passive'
    ical_export_token TEXT NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS villa_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    villa_id INTEGER NOT NULL,
    image_url TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0,
    is_cover INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS reservations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    villa_id INTEGER NOT NULL,
    start_date TEXT NOT NULL, -- YYYY-MM-DD
    end_date TEXT NOT NULL,   -- YYYY-MM-DD
    status TEXT NOT NULL DEFAULT 'booked', -- 'booked', 'blocked', 'optional'
    source TEXT NOT NULL DEFAULT 'manual', -- 'manual', 'airbnb', 'booking', 'external_ical'
    external_calendar_id INTEGER,
    customer_name TEXT,
    customer_phone TEXT,
    agency_name TEXT,
    price REAL,
    down_payment REAL,
    remaining_payment REAL,
    booking_date TEXT, -- YYYY-MM-DD (Rezervasyon Alınma Tarihi)
    note TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE,
    FOREIGN KEY (external_calendar_id) REFERENCES external_calendars(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS price_periods (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    villa_id INTEGER NOT NULL,
    start_date TEXT NOT NULL, -- YYYY-MM-DD
    end_date TEXT NOT NULL,   -- YYYY-MM-DD
    nightly_price REAL NOT NULL,
    weekly_price REAL,
    min_stay_nights INTEGER,
    title TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS external_calendars (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    villa_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    ical_url TEXT NOT NULL,
    source_type TEXT DEFAULT 'generic_ical', -- 'airbnb', 'booking', 'generic_ical'
    last_synced_at DATETIME,
    sync_status TEXT DEFAULT 'pending', -- 'pending', 'success', 'failed'
    last_error TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    action TEXT NOT NULL,
    entity_type TEXT NOT NULL,
    entity_id INTEGER,
    details TEXT, -- JSON string
    ip_address TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token TEXT NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for optimal calendar querying & tenant protection
CREATE INDEX IF NOT EXISTS idx_villas_user ON villas(user_id);
CREATE INDEX IF NOT EXISTS idx_res_dates ON reservations(villa_id, start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_price_dates ON price_periods(villa_id, start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_ext_cal_villa ON external_calendars(villa_id);
CREATE INDEX IF NOT EXISTS idx_user_tokens ON user_tokens(token);
