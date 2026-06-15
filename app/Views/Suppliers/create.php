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
$consulta = "SELECT * FROM proveedores LIMIT $inicio, $registros_por_pagina";
$resultados = $mysqli->query($consulta);

// Verificar si la consulta fue exitosa
if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Calcular el número total de registros
$total_registros_query = "SELECT COUNT(*) AS total FROM   proveedores";
$total_resultados = $mysqli->query($total_registros_query);
$total_registros = $total_resultados->fetch_assoc()['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);





// Obtener el término de búsqueda
$searchQuery = isset($_GET['search-query']) ? $_GET['search-query'] : '';

// Construir la consulta SQL con filtro si hay búsqueda
if (!empty($searchQuery)) {
    $consulta = "SELECT * FROM proveedores WHERE 
                 nombre_empresa LIKE '%$searchQuery%' OR 
                 direccion LIKE '%$searchQuery%' OR 
                 telefono LIKE '%$searchQuery%'
                 ORDER BY id_proveedor";
} else {
    $consulta = "SELECT * FROM proveedores ORDER BY id_proveedor";
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
    <title>Modulo de proveedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<style>
body {
    background-color: #000;
    color: #f5f5f5;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.table-container {
    background-color: #1a1a1a; 
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.4);
}

.table {
    max-width: 1200px; 
    margin: 0 auto; /* centra horizontalmente */
    overflow-x: auto; /* para que en móviles haya scroll horizontal */
    background-color: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 12px rgba(0,0,0,0.1);
}

.table th {
    background-color: hsl(263, 93.20%, 17.30%);
    color: white;
    text-align: center;
    font-weight: bold;
    padding: 12px;
}

.table td {
    padding: 10px;
    color: #333;
}

.table tbody tr:nth-child(odd) {
    background-color: #f2f2f2;
}

.table tbody tr:hover {
    background-color: #d1ecf1;
    transition: background-color 0.3s;
}

.panel {
    display: flex;
    justify-content: space-between;
    border: 1px solid #333;
    padding: 20px;
    border-radius: 8px;
    background-color: hsl(240, 0.90%, 21.00%);
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    flex-wrap: wrap;
    gap: 15px;
}

.column {
    width: 48%;
}

h2 {
    color: whitesmoke;
    margin-bottom: 15px;
}

.nav {
    display: flex;
    align-items: center;
    float: left;
    margin-left: 20px;
}

.nav a {
    color: whitesmoke;
    text-decoration: none;
    padding: 10px;
    font-size: 16px;
    margin-left: 10px;
    border-radius: 4px;
    transition: all 0.2s ease-in-out;
}

.nav a:hover {
    background-color: darkblue;
    color: #000;
}

.nav .active {
    color: #ff6f61;
}

.btn {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background-color: #333;
    color: #ff6f61;
    transition: background-color 0.3s, color 0.3s;
}

.btn:hover {
    background-color: #ff6f61;
    color: #fff;
}

.icono-editar {
  color: #007bff; /* azul Bootstrap */
  font-size: 20px;
  transition: 0.3s;
}

.icono-editar:hover {
  color: #0056b3;
}

.icono-borrar {
  color: #dc3545; /* rojo Bootstrap */
  font-size: 20px;
  transition: 0.3s;
}

.icono-borrar:hover {
  color: #a71d2a;
}

  .mi-estilo-modal {
    border: 3px solid #007bff;
    border-radius: 15px;
    box-shadow: 0 0 15px rgba(0,0,0,0.4);
  }

  .modal-body {
    font-size: 16px;
    color: #333;
  }

  .modal-body p span {
    font-weight: bold;
    color: #0066cc;
  }

  .modal-title{
    color: #000;
  }

.ck-editor__editable {
    min-height: 150px;
}
</style>



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

    <div class="container-fluid">

    <!-- Búsqueda -->
    <form method="GET" class="d-flex justify-content-center mb-4">
        <input type="text"
               name="search-query"
               class="form-control w-50 me-2"
               placeholder="Buscar proveedor..."
               value="<?= htmlspecialchars($searchQuery); ?>">

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Buscar
        </button>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">

            <thead class="table-primary">
                <tr>
                    <th>ID</th>
                    <th>Empresa</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>

            <?php while ($proveedor = $resultados->fetch_assoc()): ?>

                <tr>

                    <td><?= $proveedor['id_proveedor']; ?></td>

                    <td>
                        <?= htmlspecialchars($proveedor['nombre_empresa']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($proveedor['telefono']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($proveedor['correo_electronico']); ?>
                    </td>

                    <td>

                        <!-- VER -->
                        <button
                            class="btn btn-info btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#proveedor<?= $proveedor['id_proveedor']; ?>">

                            <i class="fas fa-eye"></i>
                        </button>

                        <!-- EDITAR -->
                        <a href="edit.php?da=Suppliers-3&lla=<?= $proveedor['id_proveedor']; ?>"
                           class="btn btn-primary btn-sm">

                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- ELIMINAR -->
                        <button
                            class="btn btn-danger btn-sm"
                            onclick="borrarProveedor(
                                <?= $proveedor['id_proveedor']; ?>,
                                '<?= $proveedor['archivo']; ?>'
                            )">

                            <i class="fas fa-trash"></i>
                        </button>

                    </td>

                </tr>

                <!-- MODAL -->
                <div class="modal fade"
                     id="proveedor<?= $proveedor['id_proveedor']; ?>"
                     tabindex="-1">

                    <div class="modal-dialog modal-lg">

                        <div class="modal-content">

                            <div class="modal-header bg-primary text-white">

                                <h5 class="modal-title">
                                    Proveedor #<?= $proveedor['id_proveedor']; ?>
                                </h5>

                                <button type="button"
                                        class="btn-close"
                                        data-bs-dismiss="modal">
                                </button>

                            </div>

                            <div class="modal-body">

                                <div class="row">

                                    <div class="col-md-6 mb-3">
                                        <strong>Producto:</strong><br>
                                        <?= htmlspecialchars($proveedor['id_producto']); ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <strong>Empresa:</strong><br>
                                        <?= htmlspecialchars($proveedor['nombre_empresa']); ?>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <strong>Dirección:</strong><br>
                                        <?= htmlspecialchars($proveedor['direccion']); ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <strong>Teléfono:</strong><br>
                                        <?= htmlspecialchars($proveedor['telefono']); ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <strong>Correo:</strong><br>
                                        <?= htmlspecialchars($proveedor['correo_electronico']); ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <strong>Condiciones de Pago:</strong><br>
                                        <?= htmlspecialchars($proveedor['condiciones_pago']); ?>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <strong>Método de Pago:</strong><br>
                                        <?= htmlspecialchars($proveedor['metodo_pago']); ?>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <strong>Descripción:</strong><br>
                                        <?= htmlspecialchars($proveedor['descripcion']); ?>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <strong>Historial de Pedidos:</strong><br>
                                        <?= htmlspecialchars($proveedor['historial_pedidos']); ?>
                                    </div>

                                    <div class="col-md-12">

                                        <strong>Archivo:</strong><br>

                                        <?php if (!empty($proveedor['archivo'])): ?>

                                            <img
                                                src="../../public/files/uploads/proveedores/<?= htmlspecialchars($proveedor['archivo']); ?>"
                                                class="img-fluid rounded border"
                                                style="max-height:250px;">

                                        <?php else: ?>

                                            <span class="text-muted">
                                                Sin archivo adjunto
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

</div>