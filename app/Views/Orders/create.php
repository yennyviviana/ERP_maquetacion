 <?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
}

define('db_host', 'localhost');
define('db_username', 'root');
define('db_password', '');
define('db_dbname', 'sofware_erp');

// Conectar a MySQL
$mysqli = mysqli_connect(db_host, db_username, db_password, db_dbname);

if (!$mysqli) {
    die('Error al conectarse a MySQL: ' . mysqli_connect_error());
}

mysqli_set_charset($mysqli, 'utf8');


// Establecer los valores de paginación......
$registros_por_pagina = 10;  // Número de registros por página
$página_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Página actual
$inicio = ($página_actual - 1) * $registros_por_pagina;  // Calcular el valor de OFFSET

// Consulta SQL para obtener los registros
$consulta = "SELECT * FROM  pedidos LIMIT $inicio, $registros_por_pagina";
$resultados = $mysqli->query($consulta);

// Verificar si la consulta fue exitosa
if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Calcular el número total de registros
$total_registros_query = "SELECT COUNT(*) AS total FROM  pedidos";
$total_resultados = $mysqli->query($total_registros_query);
$total_registros = $total_resultados->fetch_assoc()['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);



// Obtener el término de búsqueda
$searchQuery = isset($_GET['search-query']) ? $_GET['search-query'] : '';

// Construir la consulta SQL con filtro si hay búsqueda
if (!empty($searchQuery)) {
    $consulta = "SELECT * FROM pedidos WHERE 
          nombre_pedido LIKE '%$searchQuery%' OR 
                 estado LIKE '%$searchQuery%' OR 
                 direccion LIKE '%$searchQuery%'
                 ORDER BY id_pedido";
} else {
    $consulta = "SELECT * FROM pedidos ORDER BY id_pedido";
}

$resultados = $mysqli->query($consulta);

if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <link href="pedido.css" type="text/css" rel="stylesheet">
</head>
<body>



    <div class="panel">
        <div class="column">
            <h2>Orders Module</h2>
            <ul class="nav">
                <li><i class="fas fa-edit icon"></i><a href='insert.php?da=Orders-2'>Order Insert</a></li>
                <li class="nav-item">
                <a class="nav-link" href="/OptimizationPRO/app/main.php">
                                <span data-feather="Home"></span>
                                Go back
                            </a>
                        </li>
            </ul>
        </div>
    </div>

    <br>
<div class="container-fluid">
    <!-- Formulario de búsqueda .....-->
    <form method="GET" class="d-flex justify-content-center mb-3">
        <input type="text" name="search-query" class="form-control w-50 me-2" 
               placeholder="Search orders..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i>Search
        </button>
    </form>



    <table class="table table-bordered table-hover">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Estado</th>
                <th>Total</th>
                <th>Fecha Pedido</th>
                <th>Fecha Entrega</th>
                <th width="220">Acciones</th>
            </tr>
        </thead>

        <tbody>

        <?php while ($pedido = $resultados->fetch_assoc()): ?>

            <tr>

                <td><?= $pedido['id_pedido']; ?></td>

                <td><?= $pedido['id_usuario']; ?></td>

                <td>

                    <?php
                    switch($pedido['estado']){
                        case 'aprobado':
                            echo '<span class="badge bg-success">Aprobado</span>';
                            break;

                        case 'cancelado':
                            echo '<span class="badge bg-danger">Cancelado</span>';
                            break;

                        case 'entregado':
                            echo '<span class="badge bg-info">Entregado</span>';
                            break;

                        default:
                            echo '<span class="badge bg-warning text-dark">Pendiente</span>';
                    }
                    ?>

                </td>

                <td>$<?= number_format($pedido['total'],2); ?></td>

                <td><?= $pedido['fecha_pedido']; ?></td>

                <td><?= $pedido['fecha_entrega']; ?></td>

                <td>

                    <button
                        class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#pedido<?= $pedido['id_pedido']; ?>">
                        <i class="fas fa-eye"></i>
                    </button>

                    <a href="edit.php?da=Orders-3&lla=<?= $pedido['id_pedido']; ?>"
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i>
                    </a>

                    <button
                        class="btn btn-danger btn-sm"
                        onclick="borrarPedido(<?= $pedido['id_pedido']; ?>)">
                        <i class="fas fa-trash-alt"></i>
                    </button>

                </td>

            </tr>

            <!-- MODAL DETALLE -->

            <div class="modal fade"
                 id="pedido<?= $pedido['id_pedido']; ?>"
                 tabindex="-1">

                <div class="modal-dialog modal-lg">

                    <div class="modal-content">

                        <div class="modal-header">

                            <h5 class="modal-title">
                                Pedido #<?= $pedido['id_pedido']; ?>
                            </h5>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal">
                            </button>

                        </div>

                        <div class="modal-body">

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <strong>Usuario:</strong><br>
                                    <?= $pedido['id_usuario']; ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Estado:</strong><br>
                                    <?= htmlspecialchars($pedido['estado']); ?>
                                </div>

                                <div class="col-12 mb-3">
                                    <strong>Dirección:</strong><br>
                                    <?= htmlspecialchars($pedido['direccion']); ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Número Seguimiento:</strong><br>
                                    <?= htmlspecialchars($pedido['numero_seguimiento']); ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Tiempo Entrega:</strong><br>
                                    <?= htmlspecialchars($pedido['tiempo_entrega_horas']); ?> horas
                                </div>

                                <div class="col-12 mb-3">
                                    <strong>Descripción:</strong><br>
                                    <?= $pedido['descripcion']; ?>
                                </div>

                                <div class="col-12 mb-3">
                                    <strong>Información:</strong><br>
                                    <?= htmlspecialchars($pedido['informacion_pedido']); ?>
                                </div>

                                <hr>

                                <div class="col-md-4">
                                    <strong>Subtotal</strong><br>
                                    $<?= number_format($pedido['subtotal'] ?? 0,2); ?>
                                </div>

                                <div class="col-md-4">
                                    <strong>Impuestos</strong><br>
                                    $<?= number_format($pedido['impuestos'] ?? 0,2); ?>
                                </div>

                                <div class="col-md-4">
                                    <strong>Total</strong><br>
                                    $<?= number_format($pedido['total'],2); ?>
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        <?php endwhile; ?>

        </tbody>

    </table>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


     
  
<script>
function borrarPedido(id, imagen) {
  if (confirm('¿Está seguro de borrar el pedido?')) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
      if (xhr.status === 200) {
        alert('Pedido eliminado correctamente.');
        location.reload();
      } else {
        alert('Error al eliminar el pedido.');
      }
    };
    xhr.send('id_pedido=' + id + '&imagen=' + imagen);
  }
}
</script>