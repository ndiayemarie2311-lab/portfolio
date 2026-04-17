<?php
// admin_avis.php — Interface pour approuver/supprimer les avis
// PROTÉGEZ cette page avec un mot de passe !

session_start();

// ── Mot de passe admin (changez-le !) ──
define('ADMIN_PASSWORD', 'ndeye2026');

require_once 'config.php';

$conn = getConnexion();
$message = '';

// Connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
    } else {
        $message = 'Mot de passe incorrect.';
    }
}

// Déconnexion
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_avis.php');
    exit;
}

// Actions (approuver / supprimer)
if ($_SESSION['admin'] ?? false) {
    if (isset($_GET['approuver'])) {
        $id = intval($_GET['approuver']);
        $conn->query("UPDATE avis SET approuve = 1 WHERE id = $id");
        header('Location: admin_avis.php');
        exit;
    }
    if (isset($_GET['supprimer'])) {
        $id = intval($_GET['supprimer']);
        $conn->query("DELETE FROM avis WHERE id = $id");
        header('Location: admin_avis.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Admin — Avis clients</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DM Sans', sans-serif; background: #0e0e0e; color: #f0f0f0; padding: 40px 24px; }
    h1 { font-size: 22px; font-weight: 500; margin-bottom: 8px; }
    p.sub { color: #888; font-size: 13px; margin-bottom: 32px; }
    .login-box { max-width: 380px; margin: 80px auto; background: #1a1a1a; border: 0.5px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 32px; }
    .login-box h2 { font-size: 18px; font-weight: 500; margin-bottom: 20px; }
    input[type=password] { width: 100%; padding: 10px 14px; background: #0e0e0e; border: 0.5px solid rgba(255,255,255,0.15); border-radius: 8px; color: #f0f0f0; font-size: 14px; margin-bottom: 14px; }
    .btn { background: #378ADD; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-size: 14px; }
    .err { color: #f09595; font-size: 13px; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; font-size: 12px; color: #888; padding: 10px 14px; border-bottom: 0.5px solid rgba(255,255,255,0.1); }
    td { padding: 14px; border-bottom: 0.5px solid rgba(255,255,255,0.07); font-size: 13px; vertical-align: top; }
    .badge { font-size: 11px; padding: 3px 10px; border-radius: 20px; }
    .badge.attente { background: #2a2200; color: #EF9F27; }
    .badge.approuve { background: #0a2e22; color: #4ecfa0; }
    .actions a { font-size: 12px; padding: 5px 12px; border-radius: 6px; margin-right: 6px; text-decoration: none; display: inline-block; }
    .actions a.ok { background: #0a2e22; color: #4ecfa0; }
    .actions a.del { background: #2e0a0a; color: #f09595; }
    .logout { font-size: 13px; color: #888; text-decoration: none; margin-left: 16px; }
    .logout:hover { color: #f0f0f0; }
    .stars { color: #EF9F27; }
  </style>
</head>
<body>

<?php if (!($_SESSION['admin'] ?? false)): ?>
  <div class="login-box">
    <h2>🔒 Administration</h2>
    <?php if ($message): ?><p class="err"><?= htmlspecialchars($message) ?></p><?php endif; ?>
    <form method="POST">
      <input type="password" name="password" placeholder="Mot de passe admin" autofocus>
      <button type="submit" class="btn">Se connecter</button>
    </form>
  </div>

<?php else: ?>

  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
    <div>
      <h1>Avis clients</h1>
      <p class="sub">Approuvez ou supprimez les avis avant publication</p>
    </div>
    <a href="?logout" class="logout">Se déconnecter</a>
  </div>

  <?php
    $result = $conn->query("SELECT * FROM avis ORDER BY approuve ASC, date_ajout DESC");
    $rows = $result->fetch_all(MYSQLI_ASSOC);
  ?>

  <?php if (empty($rows)): ?>
    <p style="color:#888;">Aucun avis pour l'instant.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Client</th>
          <th>Message</th>
          <th>Note</th>
          <th>Statut</th>
          <th>Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $avis): ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($avis['nom']) ?></strong><br>
            <span style="color:#888; font-size:12px;"><?= htmlspecialchars($avis['role']) ?></span>
          </td>
          <td style="max-width:300px; color:#aaa;"><?= htmlspecialchars($avis['message']) ?></td>
          <td class="stars"><?= str_repeat('★', $avis['note']) ?></td>
          <td>
            <?php if ($avis['approuve']): ?>
              <span class="badge approuve">Publié</span>
            <?php else: ?>
              <span class="badge attente">En attente</span>
            <?php endif; ?>
          </td>
          <td style="color:#888; white-space:nowrap;"><?= date('d/m/Y', strtotime($avis['date_ajout'])) ?></td>
          <td class="actions">
            <?php if (!$avis['approuve']): ?>
              <a href="?approuver=<?= $avis['id'] ?>" class="ok">✓ Approuver</a>
            <?php endif; ?>
            <a href="?supprimer=<?= $avis['id'] ?>" class="del" onclick="return confirm('Supprimer cet avis ?')">✕ Supprimer</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

<?php endif; ?>

</body>
</html>
<?php $conn->close(); ?>