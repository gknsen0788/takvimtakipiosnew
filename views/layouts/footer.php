</main>
</div>
<?php if (\App\Auth::check() && empty($hideNav)): ?>
</div>
<?php endif; ?>

<footer class="bg-white border-t border-slate-200 mt-auto py-3 sm:py-4 text-center text-xs text-slate-500 w-full shrink-0 relative z-30" style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0px));">
    <div class="w-full max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        &copy; <?= date('Y') ?> <strong>TakvimTakip</strong> - www.trendvillas.net güvencesiyle. Tüm hakları saklıdır.
    </div>
</footer>

<?php if (!empty($hideNav)): ?>
<script>
(function() {
    function adjustAuthHeight() {
        var wrapper = document.getElementById('mainContentWrapper');
        var footer = document.querySelector('footer');
        if (wrapper && footer) {
            var vh = window.innerHeight || document.documentElement.clientHeight;
            var footerH = footer.offsetHeight || 50;
            var targetH = vh - footerH;
            if (targetH > 350) {
                wrapper.style.minHeight = targetH + 'px';
            }
        }
    }
    adjustAuthHeight();
    window.addEventListener('resize', adjustAuthHeight);
    window.addEventListener('orientationchange', adjustAuthHeight);
    document.addEventListener('DOMContentLoaded', adjustAuthHeight);
})();
</script>
<?php endif; ?>

</body>
</html>

