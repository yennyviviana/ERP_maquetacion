<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DBNAME', 'sofware_erp');

// Conectar a MySQL
$mysqli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DBNAME);

if ($mysqli->connect_error) {
    die('Error al conectarse a MySQL: ' . $mysqli->connect_error);
}

$mysqli->set_charset('utf8');




// Establecer los valores de paginación
$registros_por_pagina = 10;  // Número de registros por página
$página_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Página actual
$inicio = ($página_actual - 1) * $registros_por_pagina;  // Calcular el valor de OFFSET

// Consulta SQL para obtener los registros
$consulta = "SELECT * FROM financieras LIMIT $inicio, $registros_por_pagina";
$resultados = $mysqli->query($consulta);

// Verificar si la consulta fue exitosa
if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Calcular el número total de registros
$total_registros_query = "SELECT COUNT(*) AS total FROM  financieras";
$total_resultados = $mysqli->query($total_registros_query);
$total_registros = $total_resultados->fetch_assoc()['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);


// Obtener el término de búsqueda
$searchQuery = isset($_GET['search-query']) ? $_GET['search-query'] : '';

// Construir la consulta SQL
$consulta = "SELECT * FROM financieras ";
if (!empty($searchQuery)) {
    $consulta .= "WHERE 
                    fecha_transaccion LIKE '%$searchQuery%' OR 
                    monto LIKE '%$searchQuery%' OR 
                    tipo_transaccion LIKE '%$searchQuery%'";
}
$consulta .= " ORDER BY id_transaccion";

$resultados = $mysqli->query($consulta);

if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Preparar datos para gráficos
$monto = [];
$fecha = [];
$monto_por_tipo = [];
while ($financiera = $resultados->fetch_assoc()) {
    $monto[] = $financiera['monto'];
    $fecha[] = $financiera['fecha_transaccion'];
    $tipo = $financiera['tipo_transaccion'];
    $monto_por_tipo[$tipo] = ($monto_por_tipo[$tipo] ?? 0) + $financiera['monto'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo financiero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="style.css" type="text/css" rel="stylesheet"> 
</head>

<body>

    <div class="panel">
        <div class="column">
            <h2>Módulo  Financiero</h2>
            <ul class="nav">
                <li><i class="fas fa-edit icon"></i><a href='insert.php?da=Financials-2'>Insert registro</a></li>
                <li class="nav-item">
                <a class="nav-link" href="/OptimizationPRO/app/main.php">
                                <span data-feather="Home"></span>
                                 Regresar
                            </a>
                        </li>
            </ul>
        </div>
    </div>

    


    <form method="GET" class="d-flex justify-content-center mt-4 mb-3">
        <input type="text" name="search-query" class="form-control w-50 me-2" 
               placeholder="Buscar pedidos..." value="<?= htmlspecialchars($searchQuery) ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>

     
    <div class="table-responsive">

    <table class="table table-hover table-bordered">

        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

        <?php $resultados->data_seek(0); ?>

        <?php while ($financiera = $resultados->fetch_assoc()): ?>

            <tr>

                <td><?= htmlspecialchars($financiera['id_transaccion']) ?></td>

                <td><?= htmlspecialchars($financiera['fecha_transaccion']) ?></td>

                <td>
                    $ <?= number_format($financiera['monto'], 2, ',', '.') ?>
                </td>

                <td>

                    <?php
                    $tipo = strtolower($financiera['tipo_transaccion']);

                    if ($tipo == 'ingreso') {
                        echo '<span class="badge bg-success">Ingreso</span>';
                    } elseif ($tipo == 'egreso') {
                        echo '<span class="badge bg-danger">Egreso</span>';
                    } else {
                        echo '<span class="badge bg-secondary">'
                            . htmlspecialchars($financiera['tipo_transaccion']) .
                            '</span>';
                    }
                    ?>

                </td>

                <td>

                    <!-- Ver -->
                    <button
                        class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#financiera<?= $financiera['id_transaccion'] ?>">

                        <i class="fas fa-eye"></i>
                    </button>

                    <!-- Editar -->
                    <a href="edit.php?lla=<?= $financiera['id_transaccion'] ?>"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-edit"></i>
                    </a>

                    <!-- Eliminar -->
                    <button
                        class="btn btn-danger btn-sm"
                        onclick="borrarFinanciera(<?= $financiera['id_transaccion'] ?>)">

                        <i class="fas fa-trash"></i>
                    </button>

                </td>

            </tr>

            <!-- Modal -->
            <div class="modal fade"
                 id="financiera<?= $financiera['id_transaccion'] ?>"
                 tabindex="-1"
                 aria-hidden="true">

                <div class="modal-dialog modal-lg">

                    <div class="modal-content">

                        <div class="modal-header bg-primary text-white">

                            <h5 class="modal-title">
                                Transacción #<?= htmlspecialchars($financiera['id_transaccion']) ?>
                            </h5>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="modal">
                            </button>

                        </div>

                        <div class="modal-body">

                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <strong>Fecha:</strong><br>
                                    <?= htmlspecialchars($financiera['fecha_transaccion']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Monto:</strong><br>
                                    $ <?= number_format($financiera['monto'], 2, ',', '.') ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Tipo:</strong><br>
                                    <?= htmlspecialchars($financiera['tipo_transaccion']) ?>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <strong>Descripción:</strong><br>
                                    <?= htmlspecialchars($financiera['descripcion']) ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Usuario:</strong><br>
                                    <?= htmlspecialchars($financiera['id_usuario']) ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Proveedor:</strong><br>
                                    <?= htmlspecialchars($financiera['id_proveedor']) ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Cliente:</strong><br>
                                    <?= htmlspecialchars($financiera['id_cliente']) ?>
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

 <!-- Paginación -->
 <nav>
        <ul class="pagination">
            <?php if ($página_actual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=1" aria-label="Primera">
                        <span aria-hidden="true">&laquo;&laquo;</span>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $página_actual - 1 ?>" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?= ($i == $página_actual) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($página_actual < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $página_actual + 1 ?>" aria-label="Siguiente">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $total_paginas ?>" aria-label="Última">
                        <span aria-hidden="true">&raquo;&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

</div>




<script>
  

    const doughnutCtx = document.getElementById('financialChartDoughnut').getContext('2d');
    const barCtx = document.getElementById('financialChartBar').getContext('2d');

    new Chart(doughnutCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($monto_por_tipo)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($monto_por_tipo)) ?>,
                backgroundColor: ['#007BFF', '#28A745', '#FF5733']
            }]
        }
    });

    new Chart(barCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($fecha) ?>,
            datasets: [{
                label: 'Monto',
                data: <?= json_encode($monto) ?>,
                backgroundColor: '#007BFF'
            }]
        }
    });
</script>


<script>
function borrarFinanciera(id) {
    if (confirm('¿Está seguro de borrar el registro?')) {
        // Realizar una petición AJAX para borrar la compra
        var xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Éxito en la eliminación de el registro financiero
                    alert('Financiero eliminada correctamente.');
                    // Recargar la página para reflejar los cambios
                    location.reload();
                } else {
                    // Error al eliminar la compra
                    alert('Error al eliminar la compra.');
                }
            }
        };

        // Configurar la petición AJAX
        xhr.open('GET', 'delete.php?lla=' + id, true);
        // Enviar la petición
        xhr.send();
    }
}
</script>



<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('input[name="search-query"]');
    const resultsTable = document.querySelector('tbody');

    searchInput.addEventListener('input', function () {
        const searchQuery = searchInput.value;

        // Crear una solicitud AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'search.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (xhr.status === 200) {
                // Actualizar la tabla con los resultados
                resultsTable.innerHTML = xhr.responseText;
            } else {
                console.error('Error al realizar la búsqueda.');
            }
        };

        xhr.send('searchQuery=' + encodeURIComponent(searchQuery));
    });
});
</script>

</body>
</html>


