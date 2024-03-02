<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$servername = "db";
$username = "chess_user";
$password = "mysql_password";
$db = "chess";

$conn = new mysqli($servername, $username, $password, $db);
if ($conn->connect_error) {
    die("Connection failed " . $conn->connect_error);
}

$games_q_str = 
    "SELECT DISTINCT GAMES.game_id AS id, GAMES.date AS date,
        outcome, w.name AS w_player, b.name AS b_player,
        rw.elo AS w_elo, rb.elo AS b_elo
    FROM MOVES
    JOIN GAMES ON MOVES.game_id = GAMES.game_id
    JOIN PLAYERS w ON white_player_id = w.player_id
    JOIN PLAYERS b ON black_player_id = b.player_id
    JOIN RATINGS rw ON white_player_id = rw.player_id 
        AND GAMES.game_id = rw.game_id
    JOIN RATINGS rb ON black_player_id = rb.player_id 
        AND rb.game_id = GAMES.game_id
    WHERE fen_before = ? ";
    
$bind_types = "s";
$bind_params = array($_GET["fen_str"]);
    
if (isset($_GET["wmin"])) {
    $games_q_str .= "AND rw.elo > ? ";
    $bind_types .= "d";
    $bind_params[] = $_GET["wmin"];
}

if (isset($_GET["wmax"])) {
    $games_q_str .= "AND rw.elo < ? ";
    $bind_types .= "d";
    $bind_params[] = $_GET["wmax"];
}

if (isset($_GET["bmin"])) {
    $games_q_str .= "AND rb.elo > ? ";
    $bind_types .= "d";
    $bind_params[] = $_GET["bmin"];
}

if (isset($_GET["bmax"])) {
    $games_q_str .= "AND rb.elo < ? ";
    $bind_types .= "d";
    $bind_params[] = $_GET["bmax"];
}

if (isset($_GET["wname"])) {
    $games_q_str .= "AND w.name LIKE ? ";
    $bind_types .= "s";
    $bind_params[] = "%" . $_GET["wname"] . "%";
}

if (isset($_GET["bname"])) {
    $games_q_str .= "AND b.name LIKE ? ";
    $bind_types .= "s";
    $bind_params[] = "%" . $_GET["bname"] . "%";
}

if (isset($_GET["result"])) {
    $games_q_str .= "AND outcome LIKE ? ";
    $bind_types .= "s";
    $bind_params[] = $_GET["result"];
}

$games_q_str .= "ORDER BY (rw.elo + rb.elo) DESC ";

$limit = 50;
if (isset($_GET["limit"]) && ((int) $_GET["limit"]) < 50) {
    $limit = $_GET["limit"];
}

$games_q_str .= "LIMIT ? ";
$bind_types .= "d";
$bind_params[] = $limit;

$resp = array();
$games_query = $conn->prepare($games_q_str . ";");
if ($games_query->bind_param($bind_types, ...$bind_params)) {
    $games_query->execute();
    $games_result = $games_query->get_result();
    while($game = $games_result->fetch_assoc()) {
        $resp[] = $game;
    }
    echo(json_encode($resp));
} else {
    die("Games query failed.");
}

?>
