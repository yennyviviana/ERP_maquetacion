<?php

session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
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



    
// Establecer los valores de paginación
$registros_por_pagina = 10;  
$página_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1; 
$inicio = ($página_actual - 1) * $registros_por_pagina;  // Calcular el valor de OFFSET

// Consulta SQL para obtener los registros
$consulta = "SELECT * FROM  proyectos LIMIT $inicio, $registros_por_pagina";
$resultados = $mysqli->query($consulta);

// Verificar si la consulta fue exitosa.....
if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Calcular el número total de registros
$total_registros_query = "SELECT COUNT(*) AS total FROM   proyectos";
$total_resultados = $mysqli->query($total_registros_query);
$total_registros = $total_resultados->fetch_assoc()['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);



// Obtener el término de búsqueda........
$searchQuery = isset($_GET['search-query']) ? $_GET['search-query'] : '';

// Construir la consulta SQL con filtro si hay búsqueda
if (!empty($searchQuery)) {
    $consulta = "SELECT * FROM proyectos WHERE 
                 nombre_proyecto LIKE '%$searchQuery%' OR 
                 estado LIKE '%$searchQuery%' OR 
                 descripcion LIKE '%$searchQuery%'
                 ORDER BY id_proyecto";
} else {
    $consulta = "SELECT * FROM proyectos ORDER BY id_proyecto";
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
    <title>Módulo de pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="style.css" type="text/css" rel="stylesheet"> 
</head>
<body>

    <div class="panel">
        <div class="column">
            <h2>Módulo de proyectos.</h2>
            <ul class="nav">
                <li><i class="fas fa-edit icon"></i><a href='insert.php?da=2'>Insert Proyects</a></li>
                <li class="nav-item">
                <a class="nav-link" href="/OptimizationPRO/app/main.php">
                                <span data-feather="Home"></span>
                                 Regresar
                            </a>
                        </li>
            </ul>
        </div>
    </div>

    <br>
<div class="container-fluid">
    

    <form method="GET" class="d-flex justify-content-center mb-3">
        <input type="text" name="search-query" class="form-control w-50 me-2" 
               placeholder="Buscar pedidos..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>

    
    <div class="table-responsive">

    <table class="table table-hover table-bordered">

        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Proyecto</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

        <?php while ($proyecto = $resultados->fetch_assoc()): ?>

            <tr>

                <td><?= htmlspecialchars($proyecto['id_proyecto']) ?></td>

                <td><?= htmlspecialchars($proyecto['nombre_proyecto']) ?></td>

                <td><?= htmlspecialchars($proyecto['fecha_inicio']) ?></td>

                <td><?= htmlspecialchars($proyecto['fecha_fin']) ?></td>

                <td>

                    <?php
                    $estado = strtolower($proyecto['estado']);

                    if ($estado == 'activo') {
                        echo '<span class="badge bg-success">Activo</span>';
                    } elseif ($estado == 'finalizado') {
                        echo '<span class="badge bg-primary">Finalizado</span>';
                    } else {
                        echo '<span class="badge bg-warning text-dark">'
                            . htmlspecialchars($proyecto['estado']) .
                            '</span>';
                    }
                    ?>

                </td>

                <td>

                    <!-- Ver -->
                    <button
                        class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#proyecto<?= $proyecto['id_proyecto'] ?>">

                        <i class="fas fa-eye"></i>
                    </button>

                    <!-- Editar -->
                    <a href="edit.php?da=3&lla=<?= $proyecto['id_proyecto'] ?>"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-edit"></i>
                    </a>

                    <!-- Eliminar -->
                    <button
                        class="btn btn-danger btn-sm"
                        onclick="borrarProyecto(
                            <?= $proyecto['id_proyecto'] ?>,
                            '<?= htmlspecialchars($proyecto['imagen_proyecto']) ?>'
                        )">

                        <i class="fas fa-trash"></i>
                    </button>

                </td>

            </tr>

            <!-- Modal -->
            <div class="modal fade"
                 id="proyecto<?= $proyecto['id_proyecto'] ?>"
                 tabindex="-1"
                 aria-hidden="true">

                <div class="modal-dialog modal-lg">

                    <div class="modal-content">

                        <div class="modal-header bg-primary text-white">

                            <h5 class="modal-title">
                                Proyecto #<?= htmlspecialchars($proyecto['id_proyecto']) ?>
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
                                    <strong>Nombre:</strong><br>
                                    <?= htmlspecialchars($proyecto['nombre_proyecto']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Usuario:</strong><br>
                                    <?= htmlspecialchars($proyecto['id_usuario']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Fecha Inicio:</strong><br>
                                    <?= htmlspecialchars($proyecto['fecha_inicio']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Fecha Fin:</strong><br>
                                    <?= htmlspecialchars($proyecto['fecha_fin']) ?>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <strong>Estado:</strong><br>
                                    <?= htmlspecialchars($proyecto['estado']) ?>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <strong>Descripción:</strong><br>
                                    <?= htmlspecialchars($proyecto['descripcion']) ?>
                                </div>

                                <div class="col-md-12">

                                    <strong>Imagen:</strong><br>

                                    <?php if (!empty($proyecto['imagen_proyecto'])): ?>

                                        <img
                                            src="../../public/img/proyecto/<?= htmlspecialchars($proyecto['imagen_proyecto']) ?>"
                                            class="img-fluid border rounded"
                                            style="max-height:300px;">

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Sin imagen disponible
                                        </span>

                                    <?php endif; ?>

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

 <!-- Paginación....... -->
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
        function borrarProyecto(id, imagen) {
            if (confirm('¿Está seguro de borrar  ?')) {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            alert('Proyecto eliminado correctamente.');
                            location.reload();
                        } else {
                            alert('Error al eliminar el estado financiero.');
                        }
                    }
                };
                xhr.open('GET', 'delete.php?lla=' + id + '&imagen=' + imagen, true);
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

        // Crear una solicitud AJAX......
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'search.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onload = function () {
            if (xhr.status === 200) {
                // Actualizar la tabla con los resultados.....
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