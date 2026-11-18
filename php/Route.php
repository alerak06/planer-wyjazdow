<?php

namespace Triptrav\Models;

use Triptrav\Core\Database;

class Route
{

    public static function create(
        $tripId,
        $title
    )
    {
        $db = Database::connect();


        $stmt = $db->prepare(
            "INSERT INTO trip_routes
            (trip_id, title)
            VALUES (?, ?)"
        );


        $stmt->bind_param(
            "is",
            $tripId,
            $title
        );


        $stmt->execute();


        return $db->insert_id;
    }



    public static function addCarStep(

        $routeId,
        $start,
        $end,
        $distance,
        $mapLink,
        $carName,
        $fuelType,
        $consumption,
        $fuelPrice,
        $roundTrip,
        $seats,
        $userId

    )
    {

        $db = Database::connect();


        // dodanie etapu
        $order = self::nextStepOrder($routeId);

        $stmt = $db->prepare(
            "INSERT INTO route_steps
            (
                route_id,
                type,
                step_order,
                start_location,
                end_location,
                distance_km,
                map_link,
                created_by
            )
            VALUES
            (?, 'car', 1, ?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "issdsi",
            $routeId,
            $start,
            $end,
            $distance,
            $mapLink,
            $userId
        );

        $stmt->execute();


        $stepId = $db->insert_id;


        // szczegóły samochodu

        $stmt = $db->prepare(
            "INSERT INTO route_car_details
            (
            step_id,
            car_name,
            fuel_type,
            consumption,
            fuel_price,
            round_trip,
            seats            
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
        "issddii",
        $stepId,
        $carName,
        $fuelType,
        $consumption,
        $fuelPrice,
        $roundTrip,
        $seats
    );


    $result = $stmt->execute();


    if($result)
    {
        self::addPassengerToCar(
            $stepId,
            $userId
        );
    }


    return $result;

    }


    public static function addPassengerToCar(
    $stepId,
    $userId
)
{
    $db = Database::connect();


    $stmt = $db->prepare(
        "INSERT IGNORE INTO route_car_passengers
        (
            step_id,
            user_id
        )
        VALUES
        (
            ?,
            ?
        )"
    );


    $stmt->bind_param(
        "ii",
        $stepId,
        $userId
    );


    if(!$stmt->execute())
    {
        die(
            "Błąd dodawania pasażera: "
            .$stmt->error
        );
    }

}

    public static function getPassengers($stepId)
{

    $db = Database::connect();


    $stmt = $db->prepare(

    "SELECT 
        users.id,
        users.username

    FROM route_steps

    JOIN users
    ON users.id = route_steps.created_by

    WHERE route_steps.id = ?

    UNION

    SELECT
        users.id,
        users.username

    FROM route_car_passengers

    JOIN users
    ON users.id = route_car_passengers.user_id

    WHERE route_car_passengers.step_id = ?

    "

    );


    $stmt->bind_param(
        "ii",
        $stepId,
        $stepId
    );


    $stmt->execute();


    return $stmt->get_result()
        ->fetch_all(MYSQLI_ASSOC);

}

    public static function joinCar(
    $stepId,
    $userId
)
{
    $db = Database::connect();


    $stmt = $db->prepare(
        "INSERT IGNORE INTO route_car_passengers
        (
            step_id,
            user_id
        )
        VALUES
        (
            ?,
            ?
        )"
    );


    $stmt->bind_param(
        "ii",
        $stepId,
        $userId
    );


    return $stmt->execute();
}


public static function isPassenger(
    $stepId,
    $userId
)
{
    $db = Database::connect();

    $stmt = $db->prepare(
        "SELECT id
        FROM route_car_passengers
        WHERE step_id = ?
        AND user_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $stepId,
        $userId
    );

    $stmt->execute();

    return $stmt
        ->get_result()
        ->num_rows > 0;
}

public static function leaveCar(
    $stepId,
    $userId
)
{
    $db = Database::connect();


    // sprawdzamy czy użytkownik jest kierowcą

    $stmt = $db->prepare(
        "SELECT created_by
        FROM route_steps
        WHERE id = ?"
    );


    $stmt->bind_param(
        "i",
        $stepId
    );


    $stmt->execute();


    $step = $stmt
        ->get_result()
        ->fetch_assoc();



    // kierowca nie może opuścić własnego auta

    if(
        $step &&
        $step['created_by'] == $userId
    )
    {
        return false;
    }



    // usuwamy pasażera

    $stmt = $db->prepare(
        "DELETE FROM route_car_passengers
        WHERE step_id = ?
        AND user_id = ?"
    );


    $stmt->bind_param(
        "ii",
        $stepId,
        $userId
    );


    return $stmt->execute();

}

public static function addPlaneStep(

    $routeId,

    $start,

    $end,

    $mapLink,

    $airline,

    $flightNumber,

    $departureAirport,

    $arrivalAirport,

    $ticketPrice,

    $departureTime,

    $bookingLink

)
{

    $db = Database::connect();


    // dodanie etapu podróży

    $order = self::nextStepOrder($routeId);


    $stmt = $db->prepare(
        "INSERT INTO route_steps
        (
            route_id,
            type,
            step_order,
            start_location,
            end_location,
            map_link
        )

        VALUES
        (
            ?,
            'plane',
            ?,
            ?,
            ?,
            ?
        )"
    );


    $stmt->bind_param(
        "iisss",
        $routeId,
        $order,
        $start,
        $end,
        $mapLink
    );


    $stmt->execute();


    $stepId = $db->insert_id;



    // szczegóły samolotu

    $stmt = $db->prepare(
        "INSERT INTO route_plane_details
        (
            step_id,
            airline,
            flight_number,
            departure_airport,
            arrival_airport,
            ticket_price,
            departure_time,
            booking_link
        )

        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)"
    );


    $stmt->bind_param(
        "issssdss",
        $stepId,
        $airline,
        $flightNumber,
        $departureAirport,
        $arrivalAirport,
        $ticketPrice,
        $departureTime,
        $bookingLink
    );


    return $stmt->execute();

}


public static function addTrainStep(

    $routeId,

    $start,

    $end,

    $mapLink,

    $carrier,

    $trainNumber,

    $ticketPrice,

    $departureTime,

    $bookingLink

)
{

    $db = Database::connect();


    $order = self::nextStepOrder($routeId);



    // dodanie etapu

    $stmt = $db->prepare(
        "INSERT INTO route_steps
        (
            route_id,
            type,
            step_order,
            start_location,
            end_location,
            map_link
        )

        VALUES
        (
            ?,
            'train',
            ?,
            ?,
            ?,
            ?
        )"
    );


    $stmt->bind_param(
        "iisss",
        $routeId,
        $order,
        $start,
        $end,
        $mapLink
    );


    $stmt->execute();


    $stepId = $db->insert_id;



    // szczegóły pociągu

    $stmt = $db->prepare(
        "INSERT INTO route_train_details
        (
            step_id,
            carrier,
            train_number,
            ticket_price,
            departure_time,
            booking_link
        )

        VALUES
        (?, ?, ?, ?, ?, ?)"
    );


    $stmt->bind_param(
        "issdss",
        $stepId,
        $carrier,
        $trainNumber,
        $ticketPrice,
        $departureTime,
        $bookingLink
    );


    return $stmt->execute();

}

public static function findByTrip($tripId)
{
    $db = Database::connect();

    $stmt = $db->prepare(
        "SELECT *
         FROM trip_routes
         WHERE trip_id = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "i",
        $tripId
    );

    $stmt->execute();

    return $stmt
        ->get_result()
        ->fetch_assoc();
}

public static function nextStepOrder($routeId)
{
    $db = Database::connect();

    $stmt = $db->prepare(
        "SELECT MAX(step_order) AS last_step
         FROM route_steps
         WHERE route_id = ?"
    );

    $stmt->bind_param(
        "i",
        $routeId
    );

    $stmt->execute();

    $result = $stmt
        ->get_result()
        ->fetch_assoc();

    return ($result["last_step"] ?? 0) + 1;
}

}