<?php
// Get top 10 moves ("san_str") that were played in a given position,
// as well as the number of times that they were played ("occs"),
// number white wins ("wwin"), number black wins ("bwin"),
// and number draws ("draws").

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


$moves_query = $conn->prepare(
                    "SELECT san_str, COUNT(*) AS occurrences, 
                        COUNT(CASE WHEN outcome = '1-0' THEN 1 END) AS wwin, 
                        COUNT(CASE WHEN outcome = '0-1' THEN 1 END) AS bwin, 
                        COUNT(CASE WHEN outcome = '1/2-1/2' THEN 1 END) AS draws 
                    FROM MOVES 
                    JOIN GAMES ON MOVES.game_id = GAMES.game_id 
                    WHERE fen_before = ? AND NOT outcome = '*'
                    GROUP BY san_str 
                    ORDER BY COUNT(*) DESC
                    LIMIT 10;");


$resp = array();
if ($moves_query->bind_param('s', $_GET['fen_str'])) {
    $moves_query->execute();
    $moves_result = $moves_query->get_result() or die("Get result error" . $conn->error);
    while($move = $moves_result->fetch_assoc()) {
        $resp[] = $move;
    }
} else {
    die("Moves query failed.");
}

echo(json_encode($resp));


?>
