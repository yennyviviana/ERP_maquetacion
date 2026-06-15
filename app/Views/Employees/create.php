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
$consulta = "SELECT * FROM  empleados LIMIT $inicio, $registros_por_pagina";
$resultados = $mysqli->query($consulta);

// Verificar si la consulta fue exitosa
if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Calcular el número total de registros
$total_registros_query = "SELECT COUNT(*) AS total FROM  empleados";
$total_resultados = $mysqli->query($total_registros_query);
$total_registros = $total_resultados->fetch_assoc()['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_registros / $registros_por_pagina);



// Obtener el término de búsqueda
$searchQuery = isset($_GET['search-query']) ? $_GET['search-query'] : '';

// Construir la consulta SQL con filtro si hay búsqueda
if (!empty($searchQuery)) {
    $consulta = "SELECT * FROM  empleados WHERE 
          nombre_completo LIKE '%$searchQuery%' OR 
                 estado LIKE '%$searchQuery%' OR 
                 direccion LIKE '%$searchQuery%'
                 ORDER BY id_empleado";
} else {
    $consulta = "SELECT * FROM  empleados ORDER BY id_empleado";
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
</head>
<body>
    
<style>
/* Estilos personalizados */
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
    margin: 0 auto;
    overflow-x: auto; 
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
  color: #007bff;
  font-size: 20px;
  transition: 0.3s;
}

.icono-editar:hover {
  color: #0056b3;
}

.icono-borrar {
  color: #dc3545; 
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
/* Ajuste de estilos para el editor CKEditor */
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

    <br>
<div class="container-fluid">
    <!-- Formulario de búsqueda.....-->
    <form method="GET" class="d-flex justify-content-center mb-3">
        <input type="text" name="search-query" class="form-control w-50 me-2" 
               placeholder="Search orders..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i>Search
        </button>
    </form>


    <div class="table-responsive">

    <table class="table table-hover table-bordered">

        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>

        <?php while ($empleado = $resultados->fetch_assoc()): ?>

            <tr>

                <td><?= htmlspecialchars($empleado['id_empleado']) ?></td>

                <td><?= htmlspecialchars($empleado['nombre_completo']) ?></td>

                <td><?= htmlspecialchars($empleado['cargo']) ?></td>

                <td><?= htmlspecialchars($empleado['departamento']) ?></td>

                <td>

                    <?php
                    $estado = strtolower($empleado['estado']);

                    if ($estado == 'activo') {
                        echo '<span class="badge bg-success">Activo</span>';
                    } elseif ($estado == 'inactivo') {
                        echo '<span class="badge bg-danger">Inactivo</span>';
                    } else {
                        echo '<span class="badge bg-secondary">'
                             . htmlspecialchars($empleado['estado']) .
                             '</span>';
                    }
                    ?>

                </td>

                <td>

                    <!-- Ver -->
                    <button
                        class="btn btn-info btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#empleado<?= $empleado['id_empleado'] ?>">

                        <i class="fas fa-eye"></i>
                    </button>

                    <!-- Editar -->
                    <a href="edit.php?da=Employees-3&lla=<?= $empleado['id_empleado'] ?>"
                       class="btn btn-primary btn-sm">

                        <i class="fas fa-edit"></i>
                    </a>

                    <!-- Eliminar -->
                    <button
                        class="btn btn-danger btn-sm"
                        onclick="borrarEmpleado(
                            <?= $empleado['id_empleado'] ?>,
                            '<?= htmlspecialchars($empleado['documento_identidad']) ?>'
                        )">

                        <i class="fas fa-trash"></i>
                    </button>

                </td>

            </tr>

            <!-- Modal -->
            <div class="modal fade"
                 id="empleado<?= $empleado['id_empleado'] ?>"
                 tabindex="-1"
                 aria-hidden="true">

                <div class="modal-dialog modal-lg">

                    <div class="modal-content">

                        <div class="modal-header bg-primary text-white">

                            <h5 class="modal-title">
                                Empleado #<?= htmlspecialchars($empleado['id_empleado']) ?>
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
                                    <strong>Nombre Completo:</strong><br>
                                    <?= htmlspecialchars($empleado['nombre_completo']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Cargo:</strong><br>
                                    <?= htmlspecialchars($empleado['cargo']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Departamento:</strong><br>
                                    <?= htmlspecialchars($empleado['departamento']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Estado:</strong><br>
                                    <?= htmlspecialchars($empleado['estado']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Tipo Documento:</strong><br>
                                    <?= htmlspecialchars($empleado['tipo_documento']) ?>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <strong>Documento:</strong><br>
                                    <?= htmlspecialchars($empleado['documento_identidad']) ?>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <strong>Dirección:</strong><br>
                                    <?= htmlspecialchars($empleado['direccion']) ?>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <strong>Teléfono:</strong><br>
                                    <?= htmlspecialchars($empleado['telefono']) ?>
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




<script>
function borrarEmpleado(id, imagen) {
  if (confirm('¿Está seguro de borrar el empleado?')) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'delete.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function () {
      if (xhr.status === 200) {
        alert('Empleado eliminado correctamente.');
        location.reload();
      } else {
        alert('Error al eliminar el  empleado.');
      }
    };
    xhr.send('id_empleado=' + id + '&imagen=' + imagen);
  }
}
</script>
