@php
    $imagePath = public_path('storage/' . $photo->photo_path);
    $imageData = '';
    $mime = 'image/jpeg';
    $imgStyle = 'display:block;margin:0 auto;';
    $maxW = $maxWidth ?? 110;
    $maxH = $maxHeight ?? 80;

    if (file_exists($imagePath)) {
        $imageData = base64_encode(file_get_contents($imagePath));
        $info = @getimagesize($imagePath);
        if ($info) {
            $mime = $info['mime'];
            $ratio = min($maxW / $info[0], $maxH / $info[1], 1);
            $imgW = max(1, (int) round($info[0] * $ratio));
            $imgH = max(1, (int) round($info[1] * $ratio));
            $imgStyle .= "width:{$imgW}px;height:{$imgH}px;";
        }
    }
@endphp
@if ($imageData)
    <img src="data:{{ $mime }};base64,{{ $imageData }}" alt="{{ $photo->description ?? 'Foto' }}" style="{{ $imgStyle }}">
@else
    <div style="background:#f1f5f9;height:{{ $maxH }}px;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:8px;text-align:center;">
        Imagen no disponible
    </div>
@endif
