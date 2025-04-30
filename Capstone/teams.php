<?php
session_start();
include("connection.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch teams created by this user
$user_id = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT team_id, team_name FROM teams WHERE created_by = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$teams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle AJAX team creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_team'])) {
    $created_by   = $user_id;
    $team_name    = $_POST['team_name'];
    $team_colors  = $_POST['team_colors'];
    $team_manager = $_POST['team_manager'];

    $insert = $con->prepare("
      INSERT INTO teams
        (team_name, team_colors, team_manager, created_by)
      VALUES (?,?,?,?)
    ");
    $insert->bind_param("sssi", $team_name, $team_colors, $team_manager, $created_by);
    if ($insert->execute()) {
        echo json_encode([
          "status"    => "success",
          "team_id"   => $insert->insert_id,
          "team_name" => $team_name
        ]);
    } else {
        echo json_encode([
          "status"  => "error",
          "message" => $insert->error
        ]);
    }
    exit;
}

// Prepare to capture CSV upload feedback
$csv_error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['upload_csv'])) {
    $team_id = intval($_POST['team_id']);

    // Ensure user owns the team
    $validate = $con->prepare("SELECT 1 FROM teams WHERE team_id = ? AND created_by = ?");
    $validate->bind_param("ii", $team_id, $user_id);
    $validate->execute();
    if ($validate->get_result()->num_rows === 0) {
        $csv_error = "You do not have permission to modify this team.";
    } else {
        $file = $_FILES['csv_file'];
        if ($file['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext === 'csv') {
                $handle    = fopen($file['tmp_name'], 'r');
                // Read & strip BOM from header
                $rawHeader = fgets($handle);
                $rawHeader = preg_replace('/^\xEF\xBB\xBF/', '', $rawHeader);
                $header    = array_map('trim', str_getcsv($rawHeader));

                // Required columns (any order, case-insensitive)
                $required  = ['First Name','Last Name','Display Name','Number','Line','Position'];
                $lowerReq  = array_map('strtolower', $required);
                $lowerAct  = array_map('strtolower', $header);
                $missing   = array_diff($lowerReq, $lowerAct);

                if (!empty($missing)) {
                    $csv_error = "Missing columns: " . implode(", ", $missing)
                               . ". Required: " . implode(", ", $required) . ".";
                } else {
                    $added = 0;
                    while (($row = fgetcsv($handle)) !== FALSE) {
                        if (count($row) < 6) {
                            continue;
                        }
                        list($first, $last, $display, $num, $line, $pos) = $row;

                        $stmt = $con->prepare("
                          INSERT INTO players
                            (team_id, first_name, last_name, display_name, number, line, position)
                          VALUES (?,?,?,?,?,?,?)
                        ");
                        $stmt->bind_param(
                          "isssiss",
                          $team_id,
                          $first,
                          $last,
                          $display,
                          $num,
                          $line,
                          $pos
                        );
                        if ($stmt->execute()) {
                            $added++;
                        }
                        $stmt->close();
                    }
                    fclose($handle);

                    if ($added < 7) {
                        $csv_error = "Only $added player(s) were added. Please add at least 7.";
                    } else {
                        header("Location: index.php");
                        exit;
                    }
                }
            } else {
                $csv_error = "Only CSV files are allowed.";
            }
        } else {
            $csv_error = "There was a problem uploading the file.";
        }
    }
    $validate->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Manage Teams | Statimate</title>
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
  <link href="assets/css/custom.css" rel="stylesheet"/>
</head>
<body class="bg-light" style="font-family:'Inter',sans-serif;">

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <i class="fas fa-drafting-compass me-1"></i> Statimate
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
          <li class="nav-item"><a class="nav-link active" href="#">Teams</a></li>
          <li class="nav-item"><a class="nav-link" href="stats.php">Stats</a></li>
          <li class="nav-item"><a class="nav-link" href="stat_sheet.php">Stat Sheets</a></li>
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
    <?php if ($csv_error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($csv_error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
      <div class="card-header">
        <h5 class="mb-0">Select or Create a Team</h5>
      </div>
      <div class="card-body">
        <form id="team_form" class="row g-3">
          <div class="col-md-6">
            <label for="team_select" class="form-label">Team</label>
            <select id="team_select" name="team_id" class="form-select">
              <option value="">-- Select a Team --</option>
              <?php foreach ($teams as $t): ?>
                <option value="<?= $t['team_id'] ?>"><?= htmlspecialchars($t['team_name']) ?></option>
              <?php endforeach; ?>
              <option value="new">+ Create New Team</option>
            </select>
          </div>
        </form>
      </div>
    </div>

    <div id="create_team_form" class="card shadow-sm mb-4" style="display:none;">
      <div class="card-header">
        <h5 class="mb-0">Create New Team</h5>
      </div>
      <div class="card-body">
        <form id="create_team" class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Name</label>
            <input type="text" name="team_name" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Colors</label>
            <input type="text" name="team_colors" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Manager</label>
            <input type="text" name="team_manager" class="form-control" required>
          </div>
          <div class="col-12 text-end">
            <button class="btn btn-primary">
              <i class="fas fa-plus me-1"></i> Create Team
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header">
        <h5 class="mb-0">Upload Players via CSV</h5>
      </div>
      <div class="card-body">
        <form id="csv_form" method="POST" enctype="multipart/form-data" class="row g-3">
          <input type="hidden" name="team_id" id="csv_team_id" value="">
          <div class="col-md-6">
            <label class="form-label">CSV File</label>
            <input type="file" name="csv_file" accept=".csv" class="form-control" required>
          </div>
          <div class="col-md-6 d-flex align-items-end justify-content-end">
            <button type="submit" name="upload_csv" class="btn btn-success">
              <i class="fas fa-upload me-1"></i> Upload CSV
            </button>
          </div>
        </form>
        <p class="mt-3 text-muted">
          Required columns (any order, case-insensitive):<br>
          First Name, Last Name, Display Name, Number, Line, Position
        </p>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
  ></script>
  <script>
    $(function(){
      $('#team_select').change(function(){
        if ($(this).val() === 'new') {
          $('#create_team_form').slideDown();
          $('#csv_team_id').val('');
        } else {
          $('#create_team_form').slideUp();
          $('#csv_team_id').val($(this).val());
        }
      });

      $('#create_team').submit(function(e){
        e.preventDefault();
        $.post('teams.php', $(this).serialize() + '&create_team=1', function(res){
          let data = JSON.parse(res);
          if (data.status === 'success') {
            let opt = $('<option>').val(data.team_id).text(data.team_name);
            $('#team_select').append(opt).val(data.team_id);
            $('#csv_team_id').val(data.team_id);
            $('#create_team_form').slideUp();
            $('#create_team')[0].reset();
          } else {
            alert('Error: ' + data.message);
          }
        });
      });
    });
  </script>
</body>
</html>
