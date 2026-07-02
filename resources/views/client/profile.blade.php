<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('app.name', 'IHUB BETS') }} - Meu Perfil</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @include('components.stylesheet')
  <link rel="stylesheet" href="{{ asset('css/client-promax.css') }}">
</head>
<body class="sidebar-mini wysihtml5-supported fixed skin-default" data-default-background-img="{{ url('img/bg.jpg') }}" data-overlay="true" data-overlay-opacity="0.35">
  <div id="app">
    <profile-component></profile-component>
  </div>
  @include('components.script-blade')
</body>
</html>
