<?php
// Given game ID, return JSON including metadata
// (player ELOs, names, event, etc) in "data" key
// and list of "san_strs" and FENs ("fen_before")
// in order that they were played under "moves" key.

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

$game_query = $conn->prepare(
                    "SELECT outcome, w.name as w_player, b.name AS b_player,
					EVENTS.name as event, GAMES.date as date,
					rw.elo as w_elo, rb.elo as b_elo
					FROM GAMES
					join EVENTS on EVENTS.event_id = GAMES.event_id
    				join PLAYERS w on white_player_id=w.player_id 
    				join PLAYERS b on black_player_id=b.player_id 
    				join RATINGS rw on white_player_id=rw.player_id 
					and GAMES.game_id=rw.game_id
    				join RATINGS rb on black_player_id=rb.player_id 
					and rb.game_id = GAMES.game_id
					where GAMES.game_id = ?;");
$resp = array();
if ($game_query->bind_param('d', $_GET['game_id'])) {
    $game_query->execute();
    $game_result = $game_query->get_result();
    $resp["data"] = $game_result->fetch_assoc();
} else {
    die("Game query failed.");
}

$moves_query = $conn->prepare(
                    "SELECT turn_no, white_to_move, san_str, fen_before
                     FROM MOVES WHERE game_id = ?
                     ORDER BY turn_no, white_to_move DESC;");
                     
if ($moves_query->bind_param('d', $_GET['game_id'])) {
    $moves_query->execute();
    $moves_result = $moves_query->get_result();
    $resp["moves"] = array();
    while($move = $moves_result->fetch_assoc()) {
        $resp["moves"][] = $move;
    }
} else {
    die("Moves query failed.");
}

echo(json_encode($resp));
?>