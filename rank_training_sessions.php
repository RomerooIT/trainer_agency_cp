<?php

function rank_training_sessions($training_sessions, $user_id, $conn) {
    $stmt = $conn->prepare('SELECT * FROM members_weights WHERE user_id = ?');
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $weights = $result->fetch_assoc();
    $stmt->close();

    if (!$weights) {
        $weights = [
            'author_weight' => 2,
            'date_weight' => 3,
            'popularity_weight' => 2,
            'discipline_weight' => 3
        ];
    }

    $ranked_training_sessions = [];

    foreach ($training_sessions as $training_session) {
        $author_score = get_author_score($training_session['ID'], $user_id, $conn);
        $date_score = get_date_score($training_session['start_time']);
        $popularity_score = get_popularity_score($training_session['ID'], $conn);
        $discipline_score = get_discipline_score($training_session['ID'], $user_id, $conn);

        $total_score = ($author_score * $weights['author_weight']) +
            ($date_score * $weights['date_weight']) +
            ($popularity_score * $weights['popularity_weight']) +
            ($discipline_score * $weights['discipline_weight']);

        $ranked_training_sessions[] = [
            'training_session' => $training_session,
            'total_score' => $total_score
        ];
    }

    usort($ranked_training_sessions, function ($a, $b) {
        return $b['total_score'] - $a['total_score'];
    });

    return $ranked_training_sessions;
}

function get_author_score($training_session_id, $user_id, $conn) {
    $stmt = $conn->prepare('
        SELECT creator_id
        FROM training_sessions
        WHERE ID = ?
    ');
    $stmt->bind_param('i', $training_session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $author_id = $row['creator_id'];

    $stmt = $conn->prepare('
        SELECT COUNT(*) AS course_count
        FROM members_training_sessions um
        JOIN training_sessions m ON um.training_session_id = m.ID
        WHERE m.creator_id = ? AND um.user_id = ?
    ');
    $stmt->bind_param('ii', $author_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $author_score = min($row['course_count'], 5); // Ограничиваем балл (например, 5)

    $stmt->close();
    return $author_score;
}

function get_date_score($start_time) {
    $current_time = time();
    $training_session_time = strtotime($start_time);
    $time_difference = $training_session_time - $current_time;

    // Если встреча в прошлом, то балл равен 0
    if ($time_difference < 0) {
        return 0;
    }

    $days_to_training_session = floor($time_difference / (60 * 60 * 24));

    if ($days_to_training_session <= 1) {
        return 4;
    } elseif ($days_to_training_session <= 3) {
        return 3;
    } elseif ($days_to_training_session <= 7) {
        return 2;
    } else {
        return 1;
    }
}

function get_popularity_score($training_session_id, $conn) {
    $stmt = $conn->prepare('SELECT COUNT(*) as participants FROM members_training_sessions WHERE training_session_id = ?');
    $stmt->bind_param('i', $training_session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $popularity_score = $row['participants'];

    $stmt->close();
    return min($popularity_score, 10);
}

function get_discipline_score($training_session_id, $user_id, $conn) {
    $stmt = $conn->prepare('
        SELECT s.title
        FROM training_sessions m
        JOIN training_sessions_disciplines ms ON m.ID = ms.training_session_id
        JOIN disciplines s ON ms.discipline_id = s.ID
        LEFT JOIN members_training_sessions um ON m.ID = um.training_session_id
        WHERE m.ID = ? AND um.user_id = ?
    ');
    $stmt->bind_param('ii', $training_session_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $discipline_score = 0;

    while ($row = $result->fetch_assoc()) {
        $discipline_score++;
    }

    $stmt->close();
    return $discipline_score;
}

?>
