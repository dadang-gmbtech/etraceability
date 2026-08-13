<div
    x-data="{
        scanning: false,
        stream: null,
        rafId: null,
        async start() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Kamera tidak tersedia. Pastikan menggunakan HTTPS atau localhost.');
                return;
            }
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: 'environment', width: { ideal: 640 }, height: { ideal: 480 } }
                });
                this.$refs.video.srcObject = this.stream;
                await this.$refs.video.play();
                this.scanning = true;
                this.tick();
            } catch(e) {
                alert('Gagal membuka kamera: ' + e.message);
            }
        },
        stop() {
            this.scanning = false;
            if (this.rafId) { cancelAnimationFrame(this.rafId); this.rafId = null; }
            if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; }
        },
        tick() {
            if (!this.scanning) return;
            const v = this.$refs.video, c = this.$refs.canvas;
            if (v && v.readyState === v.HAVE_ENOUGH_DATA) {
                c.width = v.videoWidth; c.height = v.videoHeight;
                const ctx = c.getContext('2d');
                ctx.drawImage(v, 0, 0);
                if (window.jsQR) {
                    const img = ctx.getImageData(0, 0, c.width, c.height);
                    const code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });
                    if (code && code.data) {
                        this.stop();
                        $wire.set('data.kode_qr_scan', code.data);
                        return;
                    }
                }
            }
            this.rafId = requestAnimationFrame(() => this.tick());
        }
    }"
    x-on:keydown.escape.window="stop()"
>
    <button
        type="button"
        x-on:click="start()"
        class="fi-btn fi-btn-size-md relative inline-grid grid-flow-col items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold shadow-sm outline-none ring-1 transition duration-75 bg-white text-gray-950 hover:bg-gray-50 ring-gray-950/10 dark:bg-white/5 dark:text-white dark:hover:bg-white/10 dark:ring-white/20"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width:18px;height:18px;flex-shrink:0;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
        </svg>
        <span>Buka Kamera</span>
    </button>
    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">
        Scan QR Code petani dengan kamera HP/laptop
    </p>

    {{-- Overlay kamera --}}
    <div
        x-show="scanning"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click.self="stop()"
        style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.88);display:flex;align-items:center;justify-content:center;"
    >
        <div style="position:relative;width:min(360px,94vw);background:#0f0f0f;border-radius:16px;overflow:hidden;box-shadow:0 30px 90px rgba(0,0,0,.8);">
            <video
                x-ref="video"
                playsinline
                muted
                style="width:100%;display:block;max-height:360px;object-fit:cover;"
            ></video>
            <canvas x-ref="canvas" style="display:none;"></canvas>

            {{-- Frame scan --}}
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;">
                <div style="width:200px;height:200px;border-radius:12px;box-shadow:0 0 0 9999px rgba(0,0,0,0.45);border:2px solid rgba(255,255,255,0.85);"></div>
            </div>

            <div style="background:rgba(0,0,0,0.65);padding:8px 16px;text-align:center;">
                <p style="color:#f3f4f6;font-size:13px;margin:0;">
                    Arahkan kamera ke QR Code petani &nbsp;·&nbsp;
                    <kbd style="background:rgba(255,255,255,0.15);padding:1px 6px;border-radius:4px;font-size:11px;">Esc</kbd>
                    untuk tutup
                </p>
            </div>

            <button
                type="button"
                x-on:click="stop()"
                title="Tutup"
                style="position:absolute;top:10px;right:10px;width:30px;height:30px;border-radius:50%;background:rgba(0,0,0,0.6);color:#fff;border:1px solid rgba(255,255,255,0.25);cursor:pointer;display:flex;align-items:center;justify-content:center;"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width:13px;height:13px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
