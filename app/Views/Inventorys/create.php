
<?php

session_start();


if(!isset($_SESSION['id_usuario'])){
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


// Establecer los valores de paginación
$registros_por_pagina = 10;  // Número de registros por página
$página_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Página actual
$inicio = ($página_actual - 1) * $registros_por_pagina;  // Calcular el valor de OFFSET

// Consulta SQL para obtener los registros
$consulta = "SELECT * FROM  inventarios LIMIT $inicio, $registros_por_pagina";
$resultados = $mysqli->query($consulta);

// Verificar si la consulta fue exitosa
if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Calcular el número total de registros
$total_registros_query = "SELECT COUNT(*) AS total FROM  inventarios";
$total_resultados = $mysqli->query($total_registros_query);
$total_registros = $total_resultados->fetch_assoc()['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);



// Obtener el término de búsqueda
$searchQuery = isset($_GET['search-query']) ? $_GET['search-query'] : '';

// Construir la consulta SQL con filtro si hay búsqueda
if (!empty($searchQuery)) {
    $consulta = "SELECT * FROM  inventarios WHERE 
                 nombre_producto LIKE '%$searchQuery%' OR 
                 descripcion LIKE '%$searchQuery%' OR 
                 categoria_productos LIKE '%$searchQuery%'
                 ORDER BY  codigo_inventario";
} else {
    $consulta = "SELECT * FROM inventarios ORDER BY  codigo_inventario";
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
            <h2>Módulo de inventarios</h2>
            <ul class="nav">
                <li><i class="fas fa-edit icon"></i><a href='insert.php?da=Inventorys-2'>Insert inventory</a></li>
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
    

    <!-- Formulario de búsqueda.....-->
    <form method="GET" class="d-flex justify-content-center mb-3">
        <input type="text" name="search-query" class="form-control w-50 me-2" 
               placeholder="Buscar pedidos..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="table-responsive">

    <table class="table table-hover table-bordered">

        <thead class="table-primary">
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Stock</th>
                <th>Precio Venta</th>
                <th>Categoría</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

        <?php while ($inventario = $resultados->fetch_assoc()): ?>

            <tr>

                <td><?= htmlspecialchars($inventario['codigo_inventario']) ?></td>

                <td><?= htmlspecialchars($inventario['nombre_producto']) ?></td>

                <td><?= htmlspecialchars($inventario['cantidad_stock']) ?></td>

                <td>
                    $<?= number_format($inventario['precio_venta'], 2, ',', '.') ?>
                </td>

                <td><?= htmlspecialchars($inventario['categoria_productos']) ?></td>

                <td>

                    <?php
                    $estado = strtolower($inventario['estado']);

                    if ($estado == 'activo') {
                        echo '<span class="badge bg-success">Activo</span>';
                    } elseif ($estado == 'agotado') {
                        echo '<span class="badge bg-danger">Agotado</span>';
                    } else {
                        echo '<span class="badge bg-warning text-dark">'
                            . htmlspecialchars($inventario['estado']) .
                            '</span>';
                    }
                    ?>

                </td>

                <td>

                    <!-- Ver -->
                    <button
                        class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#inventario<?= $inventario['codigo_inventario'] ?>">

                        <i class="fas fa-eye"></i>
                    </button>

                    <!-- Editar -->
                    <a href="edit.php?da=3&lla=<?= $inventario['codigo_inventario'] ?>"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-edit"></i>
                    </a>

                    <!-- Eliminar -->
                    <button
                        class="btn btn-danger btn-sm"
                        onclick="borrarInventario(<?= $inventario['codigo_inventario'] ?>)">

                        <i class="fas fa-trash"></i>
                    </button>

                </td>

            </tr>

            <!-- Modal -->
            <div class="modal fade"
                 id="inventario<?= $inventario['codigo_inventario'] ?>"
                 tabindex="-1"
                 aria-hidden="true">

                <div class="modal-dialog modal-lg">

                    <div class="modal-content">

                        <div class="modal-header bg-primary text-white">

                            <h5 class="modal-title">
                                Inventario #<?= htmlspecialchars($inventario['codigo_inventario']) ?>
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
                                    <strong>Producto:</strong><br>
                                    <?= htmlspecialchars($inventario['nombre_producto']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Categoría:</strong><br>
                                    <?= htmlspecialchars($inventario['categoria_productos']) ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Stock:</strong><br>
                                    <?= htmlspecialchars($inventario['cantidad_stock']) ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Precio Unitario:</strong><br>
                                    $<?= number_format($inventario['precio_unitario'], 2, ',', '.') ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Costo Unitario:</strong><br>
                                    $<?= number_format($inventario['costo_unitario'], 2, ',', '.') ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Precio Compra:</strong><br>
                                    $<?= number_format($inventario['precio_compra'], 2, ',', '.') ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Precio Venta:</strong><br>
                                    $<?= number_format($inventario['precio_venta'], 2, ',', '.') ?>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <strong>Estado:</strong><br>
                                    <?= htmlspecialchars($inventario['estado']) ?>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <strong>Descripción:</strong><br>
                                    <?= htmlspecialchars($inventario['descripcion']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Código de Barras:</strong><br>
                                    <?= htmlspecialchars($inventario['codigo_barras']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Ubicación:</strong><br>
                                    <?= htmlspecialchars($inventario['ubicacion']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Proveedor:</strong><br>
                                    <?= htmlspecialchars($inventario['id_proveedor']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>ID Producto:</strong><br>
                                    <?= htmlspecialchars($inventario['id_producto']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Fecha Adquisición:</strong><br>
                                    <?= htmlspecialchars($inventario['fecha_adquisicion']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Fecha Vencimiento:</strong><br>
                                    <?= htmlspecialchars($inventario['fecha_vencimiento']) ?>
                                </div>

                                <div class="col-md-12">
                                    <strong>Documento:</strong><br>

                                    <?php if (!empty($inventario['tipo_documento'])): ?>
                                        <img
                                            src="../../public/img/TipoDocumento/<?= htmlspecialchars($inventario['tipo_documento']) ?>"
                                            class="img-fluid border rounded"
                                            style="max-height:250px;">
                                    <?php else: ?>
                                        <span class="text-muted">
                                            Sin documento
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
function borrarInventario(id, imagen) {
    if (confirm('¿Está seguro de borrar el  inventario?')) {
        // Realizar una petición AJAX para borrar el inventario
        var xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Éxito en la eliminación del  inventario
                    alert('Pedido eliminado correctamente.');
                    // Recargar la página para reflejar los cambios
                    location.reload();
                } else {
                    // Error al eliminar el  inventario
                    alert('Error al eliminar el  inventario.');
                }
            }
        };

        // Configurar la petición AJAX
        xhr.open('GET', 'delete.php?lla=' + id + '&imagen=' + imagen, true);
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
