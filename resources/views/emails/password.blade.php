<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Réinitialisation mot de passe</title>
</head>
<body>
  <h2>Réinitialisation du mot de passe</h2>

  <p>Bonjour {{ $user->name }},</p>

  <p>Vous (ou quelqu'un utilisant votre adresse) avez demandé à réinitialiser le mot de passe administrateur.</p>

  <p>
    <a href="{{ $url }}" style="display:inline-block;padding:10px 18px;border-radius:6px;text-decoration:none;border:1px solid #222;">
      Réinitialiser mon mot de passe
    </a>
  </p>

  <p>Si vous n'avez pas demandé cette action, ignorez simplement cet email.</p>

  <p>— {{ config('app.name') }}</p>
</body>
</html>
