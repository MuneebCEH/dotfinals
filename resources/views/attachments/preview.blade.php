{{-- resources/views/attachments/preview.blade.php --}}
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="color-scheme" content="dark light">
  <style>
    html,body{margin:0;background:#111;color:#fff}
    #viewer{padding:16px;text-align:center}
    canvas{display:block;margin:0 auto 16px;background:#fff;border-radius:12px}
    *{user-select:none;-webkit-user-select:none;-webkit-touch-callout:none}
    @media print{body{display:none!important}}
  </style>
</head>
<body oncontextmenu="return false;">
  <div id="viewer">Loading…</div>

  <!-- Load PDF.js (use Option A local files or Option B CDN) -->
  <script src="/vendor/pdfjs/pdf.min.js"></script>
  <script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = "/vendor/pdfjs/pdf.worker.min.js";

    const fileUrl = @json($fileUrl);
    const viewer = document.getElementById('viewer');

    document.addEventListener('keydown', e => {
      const k = e.key.toLowerCase();
      if ((e.ctrlKey||e.metaKey) && ['s','p','c','x','v','a'].includes(k)) e.preventDefault();
      if (e.key === 'PrintScreen') e.preventDefault();
    }, true);

    (async () => {
      try {
        const pdf = await pdfjsLib.getDocument({ url: fileUrl }).promise;
        viewer.textContent = '';
        for (let i = 1; i <= pdf.numPages; i++) {
          const page = await pdf.getPage(i);
          const viewport = page.getViewport({ scale: 1.25 });
          const canvas = document.createElement('canvas');
          const ctx = canvas.getContext('2d');
          canvas.width = viewport.width;
          canvas.height = viewport.height;
          viewer.appendChild(canvas);
          await page.render({ canvasContext: ctx, viewport }).promise;
        }
      } catch (err) {
        viewer.textContent = 'Failed to load PDF.';
        console.error(err);
      }
    })();
  </script>
</body>
</html>
