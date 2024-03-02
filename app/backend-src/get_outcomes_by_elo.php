<?php
// For each range of 200 ELO of players (e.g. 2000-2199, 2200-2399, etc.),
// represented as "elor" key, return no occurrences ("occs"), white wins ("wwin"),
// black wins ("bwin"), and draws ("draw") from a given positon ("fen_str", 
// query param).
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


// FLOOR(w.elo / 200) * 200 divides white ELO into
// ranges of 200; i.e. all ELOs between 2000 and 2199
// are grouped into this.
// Note that we only care about games where *both*
// players are in this range. This is why we can
// use w.elo in this formula, since we also check that
// black ELO is between FLOOR(w.elo / 200) * 200 and
// FLOOR(w.elo / 200) * 200 + 200.
$outcomes_query = $conn->prepare(
                "SELECT (FLOOR(w.elo / 200) * 200) AS elor,
                    COUNT(*) AS occs,
                    COUNT(CASE WHEN outcome = '1-0' THEN 1 END) AS wwin,
                    COUNT(CASE WHEN outcome = '0-1' THEN 1 END) AS bwin,
                    COUNT(CASE WHEN outcome = '1/2-1/2' THEN 1 END) AS draw
                FROM MOVES
                JOIN GAMES ON MOVES.game_id = GAMES.game_id
                JOIN RATINGS w ON white_player_id = w.player_id 
                    AND GAMES.game_id = w.game_id
                JOIN RATINGS b ON black_player_id = b.player_id 
                    AND b.game_id = GAMES.game_id
                WHERE fen_before = ?
                    AND NOT w.elo = ''
                    AND NOT outcome = '*'
                    AND FLOOR((w.elo / 200) * 200) >= 2000
                    AND b.elo > (FLOOR(w.elo / 200) * 200)
                    AND b.elo < (FLOOR(w.elo / 200) * 200) + 200
                    AND w.elo < (FLOOR(w.elo / 200) * 200) + 200
                GROUP BY (FLOOR(w.elo / 200) * 200)
                ORDER BY (FLOOR(w.elo / 200) * 200) DESC;");

$resp = array();
if ($outcomes_query->bind_param('s', $_GET['fen_str'])) {
    $outcomes_query->execute();
    $outcomes_result = $outcomes_query->get_result();
    while($outcome = $outcomes_result->fetch_assoc()) {
        $resp[] = $outcome;
    }
} else {
    die("Outcomes query failed.");
}

echo(json_encode($resp));
?>