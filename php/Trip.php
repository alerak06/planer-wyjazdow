<?php

namespace Triptrav\Models;

use Triptrav\Core\Database;

class Trip
{

   public static function all()
{
    $db = Database::connect();

    $query = $db->query(
        "SELECT * FROM trips ORDER BY created_at DESC"
    );

    return $query->fetch_all(MYSQLI_ASSOC);
}

public static function create(
    $title,
    $description,
    $tripDate,
    $ownerId
)
{
    $db = Database::connect();

    $shareCode = bin2hex(random_bytes(8));


    $stmt = $db->prepare(
        "INSERT INTO trips
        (title, description, trip_date, owner_id, share_code)
        VALUES (?, ?, ?, ?, ?)"
    );


    $stmt->bind_param(
        "sssis",
        $title,
        $description,
        $tripDate,
        $ownerId,
        $shareCode
    );


    $stmt->execute();


    $tripId = $db->insert_id;


    $stmt = $db->prepare(
        "INSERT INTO trip_users
        (trip_id, user_id, role)
        VALUES (?, ?, 'owner')"
    );


    $stmt->bind_param(
        "ii",
        $tripId,
        $ownerId
    );


    $stmt->execute();


    return $tripId;
}

public static function findByShareCode($shareCode)
{
    $db = Database::connect();

    $stmt = $db->prepare(
        "SELECT 
            trips.*,
            users.username AS owner_name
        FROM trips
        LEFT JOIN users 
        ON trips.owner_id = users.id
        WHERE trips.share_code = ?"
    );

    $stmt->bind_param(
        "s",
        $shareCode
    );

    $stmt->execute();

    $result = $stmt->get_result();

    return $result->fetch_assoc();
}

public static function users($tripId)
{
    $db = Database::connect();

    $stmt = $db->prepare(
        "SELECT 
            users.username,
            trip_users.role
        FROM trip_users
        JOIN users 
        ON trip_users.user_id = users.id
        WHERE trip_users.trip_id = ?"
    );


    $stmt->bind_param(
        "i",
        $tripId
    );


    $stmt->execute();


    $result = $stmt->get_result();


    return $result->fetch_all(MYSQLI_ASSOC);
}

public static function hasUser($tripId, $userId)
{
    $db = Database::connect();

    $stmt = $db->prepare(
        "SELECT id 
        FROM trip_users 
        WHERE trip_id = ? 
        AND user_id = ?"
    );


    $stmt->bind_param(
        "ii",
        $tripId,
        $userId
    );


    $stmt->execute();


    $result = $stmt->get_result();


    return $result->num_rows > 0;
}

public static function join($tripId, $userId)
{
    $db = Database::connect();

    $stmt = $db->prepare(
        "INSERT INTO trip_users
        (trip_id, user_id, role)
        VALUES (?, ?, 'participant')"
    );


    $stmt->bind_param(
        "ii",
        $tripId,
        $userId
    );


    return $stmt->execute();
}

public static function setDate($tripId, $date)
{
    $db = Database::connect();


    $stmt = $db->prepare(
        "UPDATE trips
        SET trip_date = ?
        WHERE id = ?"
    );


    $stmt->bind_param(
        "si",
        $date,
        $tripId
    );


    return $stmt->execute();
}

}