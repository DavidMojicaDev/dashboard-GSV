<?php

include('essentials.php');
$action = isset($_POST['action']) ? $_POST['action'] : '';
$anio = isset($_POST['anio']) ? $_POST['anio'] : null;

switch ($action) {
    case 'getDataChart1':
        returnDataResponse(getDataChart1($anio));
        break;
    case 'getDataChart2':
        returnDataResponse(getDataChart2($anio));
        break;
    case 'getDataChart3':
        returnDataResponse(getDataChart3($anio));
        break;
    case 'getDataChart4':
        returnDataResponse(getDataChart4($anio));
        break;
    case 'getDataChart5':
        returnDataResponse(getDataCircular($anio, 1));
        break;
    case 'getDataChart6':
        returnDataResponse(getDataCircular($anio, 2));
        break;
}
function getDataPareto($anio, $tpa){
    include('PDOconn.php');

    $query1 = "SELECT a.mes as mes, SUM(a.cantidad) as value
            FROM tbl_accidente a
            WHERE a.tipo_accidente = :tpa and a.anio = :anio
            GROUP BY a.mes
            ORDER BY a.mes ASC;";
    $stmt = $pdo->prepare($query1);
    $stmt->bindParam(":tpa", $tpa, PDO::PARAM_INT);
    $stmt->bindParam(":anio", $anio, PDO::PARAM_INT);
    $stmt->execute();
    
    $result1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $query2 = "SELECT SUM(p.cantidad) as VALUE
    FROM tbl_poblacion p;";
    $stmt = $pdo->prepare($query2);
    $stmt->execute();

    $result2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [$result1, $result2];
}


function getDataCircular($municipio, $tpa){
    include('PDOconn.php');

    if (is_numeric($municipio)) {
        $query = "SELECT SUM(a.cantidad) as value, v.nombre as name
        FROM tbl_accidente a
        JOIN tbl_vehiculo v on a.vehiculo = v.id
        WHERE a.municipio = :mpio AND a.tipo_accidente = :tpa
        GROUP BY v.id;";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':mpio', $municipio, PDO::PARAM_INT);
        $stmt->bindParam(':tpa', $tpa, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $query = "SELECT SUM(a.cantidad) as value, v.nombre as name
    FROM tbl_accidente a
    JOIN tbl_vehiculo v on a.vehiculo = v.id
    WHERE a.tipo_accidente = :tpa
    GROUP BY v.id;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':tpa', $tpa, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getDataChart1($anio){
    include('PDOconn.php');

    $anioMinimo = 2018;
    $anioActual = date('Y');

    if (is_numeric($anio)) {
        if ($anio >= $anioMinimo && $anio <= $anioActual) {
            $query = "SELECT SUM(a.cantidad) as value, v.nombre as name
            FROM tbl_accidente a
            JOIN tbl_vehiculo v ON a.vehiculo = v.id
            WHERE a.anio = :anio
            GROUP BY v.nombre";

            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    $query = "SELECT SUM(a.cantidad) as value, v.nombre as name
        FROM tbl_accidente a
        JOIN tbl_vehiculo v ON a.vehiculo = v.id
        GROUP BY v.nombre;";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getDataChart4($municipio)
{
    include('PDOconn.php');
    $anioMinimo = 2018;
    $anioActual = date('Y');

    if (is_numeric($municipio)) {
        $query = "SELECT a.anio, m.nombre as mes, SUM(a.cantidad) as total_muertes 
        FROM tbl_accidente a
        JOIN tbl_meses m ON a.mes = m.id
        WHERE a.tipo_accidente = 1 AND a.anio BETWEEN :a_m AND :a_y AND a.municipio = :municipio
        GROUP BY a.anio, m.nombre
        ORDER BY a.anio, CAST(a.mes AS SIGNED);";

        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":a_m", $anioMinimo, PDO::PARAM_INT);
        $stmt->bindParam(":a_y", $anioActual, PDO::PARAM_INT);
        $stmt->bindParam(":municipio", $municipio, PDO::PARAM_INT);

        $stmt->execute();
        $resultQ1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT * from  tbl_poblacion where id_municipio = :municipio";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":municipio", $municipio, PDO::PARAM_INT);
        $stmt->execute();
        $resultQ2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [$resultQ1, $resultQ2];
    }
    $query = "SELECT a.anio, m.nombre as mes, SUM(a.cantidad) as total_muertes 
            FROM tbl_accidente a
            JOIN tbl_meses m ON a.mes = m.id
            WHERE a.tipo_accidente = 1 AND a.anio BETWEEN :a_m AND :a_y 
            GROUP BY a.anio, m.nombre
            ORDER BY a.anio, CAST(a.mes AS SIGNED);";

    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":a_m", $anioMinimo, PDO::PARAM_INT);
    $stmt->bindParam(":a_y", $anioActual, PDO::PARAM_INT);
    $stmt->execute();
    $resultQ1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query = "SELECT SUM(p.cantidad) as pob_total
    from tbl_poblacion p;";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $resultQ2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [$resultQ1, $resultQ2];
}

function getDataChart3($municipio)
{
    include('PDOconn.php');
    $anioMinimo = 2018;
    $anioActual = date('Y');


    if (is_numeric($municipio)) {
        $query = "SELECT a.anio, m.nombre as mes, SUM(a.cantidad) as total_muertes 
        FROM tbl_accidente a
        JOIN tbl_meses m ON a.mes = m.id
        WHERE a.tipo_accidente = 1 AND a.anio BETWEEN :a_m AND :a_y AND a.municipio = :municipio
        GROUP BY a.anio, m.nombre
        ORDER BY a.anio, CAST(a.mes AS SIGNED);";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":municipio", $municipio, PDO::PARAM_INT);
        $stmt->bindParam(":a_m", $anioMinimo, PDO::PARAM_INT);
        $stmt->bindParam(":a_y", $anioActual, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $query = "SELECT a.anio, m.nombre as mes, SUM(a.cantidad) as total_muertes 
            FROM tbl_accidente a
            JOIN tbl_meses m ON a.mes = m.id
            WHERE a.tipo_accidente = 1 AND a.anio BETWEEN :a_m AND :a_y
            GROUP BY a.anio, m.nombre
            ORDER BY a.anio, CAST(a.mes AS SIGNED);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":a_m", $anioMinimo, PDO::PARAM_INT);
    $stmt->bindParam(":a_y", $anioActual, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDataChart2($anio)
{
    include('PDOconn.php');
    $anioMinimo = 2018;
    $anioActual = date('Y');

    if (is_numeric($anio)) {
        if ($anio >= $anioMinimo && $anio <= $anioActual) {
            $query = "SELECT v.nombre as nombre_vehiculo, t.nombre as tipo_accidente, SUM(a.cantidad) as total_accidentes
            FROM tbl_accidente a
            JOIN tbl_vehiculo v ON a.vehiculo = v.id
            JOIN tbl_tipo_accidente t ON a.tipo_accidente = t.id
            WHERE a.anio = :anio
            GROUP BY v.nombre, t.nombre";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':anio', $anio, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    $query = "SELECT v.nombre as nombre_vehiculo, t.nombre as tipo_accidente, SUM(a.cantidad) as total_accidentes
    FROM tbl_accidente a
    JOIN tbl_vehiculo v ON a.vehiculo = v.id
    JOIN tbl_tipo_accidente t ON a.tipo_accidente = t.id
    GROUP BY v.nombre, t.nombre";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

