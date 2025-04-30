<?php
session_start();
include("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Fetch only this user’s teams
$stmt = $con->prepare("SELECT team_id, team_name FROM teams WHERE created_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Which team & sort params?
$selected_team_id = $_GET['team_id'] ?? '';
$allowed_cols = ['name','goals','assists','throwaways','drops','blocks','completed_passes','pass_completion_rate'];
$allowed_ord  = ['ASC','DESC'];

$order_by = $_GET['order_by']  ?? 'name';
$order    = $_GET['order']     ?? 'ASC';
$order_by = in_array($order_by, $allowed_cols) ? $order_by : 'name';
$order    = in_array(strtoupper($order), $allowed_ord) ? strtoupper($order) : 'ASC';

// Build ORDER BY clause only if a team is selected
$order_clause = '';
if ($selected_team_id) {
    if ($order_by === 'name') {
        $order_clause = " ORDER BY p.last_name $order, p.first_name $order";
    } else {
        $order_clause = " ORDER BY $order_by $order";
    }
}

// If team chosen, fetch aggregated stats
$players = [];
if ($selected_team_id) {
    $sql = "
      SELECT 
        p.player_id,
        p.first_name,
        p.last_name,
        SUM(CASE WHEN ps.stat_type='goal' THEN ps.value ELSE 0 END) AS goals,
        SUM(CASE WHEN ps.stat_type='assist' THEN ps.value ELSE 0 END) AS assists,
        SUM(CASE WHEN ps.stat_type='throwaway' THEN ps.value ELSE 0 END) AS throwaways,
        SUM(CASE WHEN ps.stat_type='drop' THEN ps.value ELSE 0 END) AS drops,
        SUM(CASE WHEN ps.stat_type='block' THEN ps.value ELSE 0 END) AS blocks,
        SUM(CASE WHEN ps.stat_type='completed-pass' THEN ps.value ELSE 0 END) AS completed_passes,
        SUM(CASE WHEN ps.stat_type IN('completed-pass','throwaway','drop') THEN ps.value ELSE 0 END) AS pass_attempts,
        CASE 
          WHEN SUM(CASE WHEN ps.stat_type IN('completed-pass','throwaway','drop') THEN ps.value ELSE 0 END)=0 THEN 0
          ELSE ROUND(
            SUM(CASE WHEN ps.stat_type='completed-pass' THEN ps.value ELSE 0 END)
            / SUM(CASE WHEN ps.stat_type IN('completed-pass','throwaway','drop') THEN ps.value ELSE 0 END)
            *100,2)
        END AS pass_completion_rate
      FROM players p
      LEFT JOIN player_stats ps ON p.player_id=ps.player_id
      WHERE p.team_id=?
      GROUP BY p.player_id,p.first_name,p.last_name"
      . $order_clause;

    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $selected_team_id);
    $stmt->execute();
    $players = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Stat Sheet | Statimate</title>
  <link 
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" 
    rel="stylesheet"
  />
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />
  <link 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" 
    rel="stylesheet"
  />
  <link href="assets/css/custom.css" rel="stylesheet" />
</head>
<body class="bg-light" style="font-family:'Inter',sans-serif;">

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-drafting-compass"></i> Statimate
      </a>
      <button class="navbar-toggler" type="button" 
              data-bs-toggle="collapse" data-bs-target="#mainNav" 
              aria-controls="mainNav" aria-expanded="false" 
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
          <li class="nav-item"><a class="nav-link active" href="#">Stat Sheets</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
          <li class="nav-item">
            <a class="nav-link" href="logout.php">
              <i class="fas fa-sign-out-alt"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container my-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h2 class="card-title text-center mb-4">Team Stat Sheet</h2>

        <form class="row g-3 align-items-center justify-content-center mb-4" method="GET" action="">
          <div class="col-auto">
            <label for="team_id" class="col-form-label">Team</label>
          </div>
          <div class="col-auto">
            <select name="team_id" id="team_id" class="form-select" onchange="this.form.submit()">
              <option value="">-- Select Team --</option>
              <?php foreach($teams as $t): ?>
                <option value="<?= $t['team_id'];?>" <?= $t['team_id']==$selected_team_id?'selected':'';?>>
                  <?= htmlspecialchars($t['team_name']);?>
                </option>
              <?php endforeach;?>
            </select>
          </div>

          <?php if($selected_team_id): ?>
          <div class="col-auto">
            <label for="order_by" class="col-form-label">Sort by</label>
          </div>
          <div class="col-auto">
            <select name="order_by" class="form-select">
              <option value="name" <?= $order_by=='name'?'selected':'';?>>Name</option>
              <option value="goals" <?= $order_by=='goals'?'selected':'';?>>Goals</option>
              <option value="assists" <?= $order_by=='assists'?'selected':'';?>>Assists</option>
              <option value="throwaways" <?= $order_by=='throwaways'?'selected':'';?>>Throwaways</option>
              <option value="drops" <?= $order_by=='drops'?'selected':'';?>>Drops</option>
              <option value="blocks" <?= $order_by=='blocks'?'selected':'';?>>Blocks</option>
              <option value="completed_passes" <?= $order_by=='completed_passes'?'selected':'';?>>Completed</option>
              <option value="pass_completion_rate" <?= $order_by=='pass_completion_rate'?'selected':'';?>>Pass %</option>
            </select>
          </div>
          <div class="col-auto">
            <select name="order" class="form-select">
              <option value="ASC" <?= $order=='ASC'?'selected':'';?>>Asc</option>
              <option value="DESC" <?= $order=='DESC'?'selected':'';?>>Desc</option>
            </select>
          </div>
          <div class="col-auto">
            <button class="btn btn-primary">Apply</button>
          </div>
          <?php endif;?>
        </form>

        <?php if($selected_team_id): ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
              <tr>
                <th>Player</th>
                <th>Goals</th>
                <th>Assists</th>
                <th>Throwaways</th>
                <th>Drops</th>
                <th>Blocks</th>
                <th>Completed</th>
                <th>Pass %</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($players as $p): 
                $rate = $p['pass_completion_rate'];
                $att  = $p['pass_attempts'];
                if ($att==0) {
                  $bg = '#ccc';
                } else {
                  $r = round(255*(1-$rate/100));
                  $g = round(255*($rate/100));
                  $bg = sprintf("#%02x%02x00",$r,$g);
                }
              ?>
              <tr>
                <td><?= htmlspecialchars($p['last_name'].', '.$p['first_name']) ?></td>
                <td><?= $p['goals'] ?></td>
                <td><?= $p['assists'] ?></td>
                <td><?= $p['throwaways'] ?></td>
                <td><?= $p['drops'] ?></td>
                <td><?= $p['blocks'] ?></td>
                <td><?= $p['completed_passes'] ?></td>
                <td style="background:<?= $bg ?>;"><?= $rate ?>%</td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <p class="text-center text-muted">Select a team above to view stats.</p>
        <?php endif;?>
      </div>
    </div>
  </div>

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
</body>
</html>
