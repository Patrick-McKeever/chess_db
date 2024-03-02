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

// Because start position is a lot harder to query than others 
// (exponential reduction in no. of games to search with each passing move)
// we simply encode this data. If the database changes, then be sure to edit 
// this.
$START_FEN_STR = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
$START_POSITION_RESULTS = '[{
    "san_str": "e4",
    "occurrences": 197683,
    "wwin": 68037,
    "bwin": 51306,
    "draws": 78340
  },
  {
    "san_str": "d4",
    "occurrences": 176849,
    "wwin": 60297,
    "bwin": 41203,
    "draws": 75349
  },
  {
    "san_str": "Nf3",
    "occurrences": 48397,
    "wwin": 16651,
    "bwin": 10805,
    "draws": 20941
  },
  {
    "san_str": "c4",
    "occurrences": 36815,
    "wwin": 13166,
    "bwin": 7921,
    "draws": 15728
  },
  {
    "san_str": "g3",
    "occurrences": 2503,
    "wwin": 916,
    "bwin": 633,
    "draws": 954
  },
  {
    "san_str": "b3",
    "occurrences": 2481,
    "wwin": 1143,
    "bwin": 775,
    "draws": 563
  },
  {
    "san_str": "f4",
    "occurrences": 788,
    "wwin": 257,
    "bwin": 329,
    "draws": 202
  },
  {
    "san_str": "Nc3",
    "occurrences": 321,
    "wwin": 102,
    "bwin": 127,
    "draws": 92
  },
  {
    "san_str": "b4",
    "occurrences": 241,
    "wwin": 96,
    "bwin": 91,
    "draws": 54
  },
  {
    "san_str": "e3",
    "occurrences": 230,
    "wwin": 90,
    "bwin": 92,
    "draws": 48
  }
]';

if($_GET["fen_str"] == $START_FEN_STR) {
    echo($START_POSITION_RESULTS);
} else {
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
}

?>
