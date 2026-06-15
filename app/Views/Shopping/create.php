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
$consulta = "SELECT * FROM  compras LIMIT $inicio, $registros_por_pagina";
$resultados = $mysqli->query($consulta);

// Verificar si la consulta fue exitosa
if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Calcular el número total de registros
$total_registros_query = "SELECT COUNT(*) AS total FROM   compras";
$total_resultados = $mysqli->query($total_registros_query);
$total_registros = $total_resultados->fetch_assoc()['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);



        // Consulta utilizando MySQLi
        $consulta = "SELECT * FROM productos ORDER BY id_producto";
        $resultados = $mysqli->query($consulta);

        // Comprobación de errores en la ejecución de la consulta
        if (!$resultados) {
            die("Error al ejecutar la consulta: " . $mysqli->error);
        }


// Obtener el término de búsqueda
$searchQuery = isset($_GET['search-query']) ? $_GET['search-query'] : '';

// Construir la consulta SQL con filtro si hay búsqueda
if (!empty($searchQuery)) {
    $consulta = "SELECT * FROM  compras WHERE 
                 productos_comprados LIKE '%$searchQuery%' OR 
                  detalles_productos LIKE '%$searchQuery%' OR 
                 estado_actual LIKE '%$searchQuery%'
                 ORDER BY id_compra";
} else {
    $consulta = "SELECT * FROM  compras ORDER BY id_compra";
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
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <title>Tu Página</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
        <link href="style.css" type="text/css" rel="stylesheet"> 
    </head>
    <body>
  
    
    <div class="panel">
        <div class="column">
            <h2>Módulo de compras</h2>
            <ul class="nav">
                <li><i class="fas fa-edit icon"></i><a href='insert.php?da=Shopping-2'>Insertar Compras</a></li>
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
               placeholder="Buscar compras..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>

    
    
   <div class="table-responsive">

<table class="table table-hover table-bordered">

    <thead class="table-primary">
        <tr>
            <th>ID</th>
            <th>Producto</th>
            <th>Total</th>
            <th>Estado</th>
            <th>Fecha Compra</th>
            <th>Proveedor</th>
            <th>Acciones</th>
        </tr>
    </thead>

    <tbody>

    <?php while ($compra = $resultados->fetch_assoc()): ?>

        <tr>

            <td><?= $compra['id_compra'] ?></td>

            <td><?= htmlspecialchars($compra['productos_comprados']) ?></td>

            <td>
                $<?= number_format($compra['total_compra'], 2) ?>
            </td>

            <td>

                <?php
                $estado = strtolower($compra['estado_actual']);

                if ($estado == 'entregado') {
                    echo '<span class="badge bg-success">Entregado</span>';
                } elseif ($estado == 'pendiente') {
                    echo '<span class="badge bg-warning text-dark">Pendiente</span>';
                } elseif ($estado == 'cancelado') {
                    echo '<span class="badge bg-danger">Cancelado</span>';
                } else {
                    echo '<span class="badge bg-secondary">'
                        . htmlspecialchars($compra['estado_actual']) .
                        '</span>';
                }
                ?>

            </td>

            <td>
                <?= htmlspecialchars($compra['fecha_compra']) ?>
            </td>

            <td>
                <?= htmlspecialchars($compra['id_proveedor']) ?>
            </td>

            <td>

                <!-- Ver -->
                <button
                    class="btn btn-info btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#compra<?= $compra['id_compra'] ?>">
                    <i class="fas fa-eye"></i>
                </button>

                <!-- Editar -->
                <a href="edit.php?da=Shopping-3&lla=<?= $compra['id_compra'] ?>"
                   class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i>
                </a>

                <!-- Eliminar -->
                <button
                    class="btn btn-danger btn-sm"
                    onclick="borrarCompra(<?= $compra['id_compra'] ?>)">
                    <i class="fas fa-trash"></i>
                </button>

            </td>

        </tr>

        <!-- Modal -->
        <div class="modal fade"
             id="compra<?= $compra['id_compra'] ?>"
             tabindex="-1">

            <div class="modal-dialog modal-lg">

                <div class="modal-content">

                    <div class="modal-header bg-primary text-white">

                        <h5 class="modal-title">
                            Compra #<?= $compra['id_compra'] ?>
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
                                <?= htmlspecialchars($compra['productos_comprados']) ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Código Inventario:</strong><br>
                                <?= htmlspecialchars($compra['codigo_inventario']) ?>
                            </div>

                            <div class="col-md-12 mb-3">
                                <strong>Detalles:</strong><br>
                                <?= htmlspecialchars($compra['detalles_productos']) ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <strong>Precio Unitario:</strong><br>
                                $<?= number_format($compra['precio_unitario'], 2) ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <strong>Precio Compra:</strong><br>
                                $<?= number_format($compra['precio_compra'], 2) ?>
                            </div>

                            <div class="col-md-4 mb-3">
                                <strong>Total Compra:</strong><br>
                                $<?= number_format($compra['total_compra'], 2) ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Estado:</strong><br>
                                <?= htmlspecialchars($compra['estado_actual']) ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Método Pago:</strong><br>
                                <?= htmlspecialchars($compra['metodo_pago']) ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Fecha Compra:</strong><br>
                                <?= htmlspecialchars($compra['fecha_compra']) ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Fecha Entrega:</strong><br>
                                <?= htmlspecialchars($compra['fecha_entrega']) ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Proveedor:</strong><br>
                                <?= htmlspecialchars($compra['id_proveedor']) ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <strong>Usuario:</strong><br>
                                <?= htmlspecialchars($compra['id_usuario']) ?>
                            </div>

                            <div class="col-md-12">

                                <strong>Factura:</strong><br>

                                <?php if (!empty($compra['factura'])): ?>

                                    <a href="../../public/img/factura-compra/<?= htmlspecialchars($compra['factura']) ?>"
                                       target="_blank">

                                        <img
                                            src="../../public/img/factura-compra/<?= htmlspecialchars($compra['factura']) ?>"
                                            class="img-fluid border rounded"
                                            style="max-height:250px;">

                                    </a>

                                <?php else: ?>

                                    <span class="text-muted">
                                        Sin factura adjunta
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
function borrarCompra(id) {
    if (confirm('¿Está seguro de borrar la compra?')) {
        // Realizar una petición AJAX para borrar la compra
        var xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Éxito en la eliminación de la compra
                    alert('Compra eliminada correctamente.');
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
