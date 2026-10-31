@props(['title' => 'Admin Panel', 'header' => null])

@include('admin.layout', [
    'title' => $title,
    'header' => $header,
    'slot' => $slot,
])
