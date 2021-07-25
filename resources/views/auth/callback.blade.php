<html>
<head>
  <meta charset="utf-8">
  <title>{{ config('app.name') }}</title>
  <script>
    window.opener.postMessage({ token: "{{ $token }}" }, "{{ url('/') }}")
  </script>
</head>
<body>
</body>
</html>
