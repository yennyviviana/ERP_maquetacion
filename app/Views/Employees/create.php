<?php

session_start();


if(!isset($_SESSION['id_usuario'])){
    header("Location: index.php");
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
    </head>
    <body>
<style>
    body {
    background-color: #000;
    color: #f5f5f5; 
}

.panel {
    display: flex;
    justify-content: space-between;
    border: 1px solid #333;
    padding: 20px;
    border-radius: 8px;
    background-color: #234DF0; 
}

.column {
    width: 48%;
}

h2 {
    color: whitesmoke; 
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
}

.btn:hover {
    background-color: #ff6f61;
    color: #fff; 
}


.btn-borrar {
    display: inline-block;
    padding: 7px 10px;
    font-size: 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background-color: #ff4d4d; 
    color: #fff;
}

.btn-borrar:hover {
    background-color: #c82333;
}

.btn-editar {
    display: inline-block;
    padding: 7px 10px;
    font-size: 14px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background-color:  blue;
    color: #fff;
}

.btn-editar:hover {
    background-color:  #0B1CDB;
}

.table-container {
    width: 100%;
    margin: 0 auto;
}

.table {
    width: 100%;
    table-layout: auto;
    word-wrap: break-word;
    color: #fff; 
    background-color: #818274; 
}

.table th, .table td {
    border: 1px solid #444;
    padding: 1rem;
    font-size: 1.1rem;
    text-align: center;
}
.table th {
    background-color: #000000;
    color:  #65D8DB; 
    font-weight: bold;
    border-bottom: 3px solid #ff6f61;
}

.table tbody tr {
    background-color: #000;
}

.table tbody tr:nth-of-type(odd) {
    background-color:  #fff; 
    color:  #000;
}



.table-responsive {
    margin-top: 1.5rem;
    overflow-x: auto;
}



    </style>
</head>
<body>



           

    <div class="panel">
        <div class="column">
            <h2>Módulo de Empleados</h2>
            <ul class="nav">
              
                <li><i class="fas fa-edit icon"></i><a href='insert.php?da=2'>Insert Employees</a></li>
    
                <li class="nav-item">
                <a class="nav-link" href="/OptimizationPRO/app/main.php">
                                <span data-feather="Home"></span>
                                 Regresar
                            </a>
                        </li>
            </ul>
        </div>
    </div>
  
    
    <div class="container-fluid">
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-light">
            <tr>
                <th scope="col">Id</th>
                <th scope="col">Nombre</th>
                <th scope="col">Cargo</th>
                <th scope="col">contratacion</th>
                <th scope="col"> Horas</th>
                <th scope="col">Precio</th>
                <th scope="col">Salario</th>
                <th scope="col">Estado</th>
                <th scope="col">Departamento</th>
                <th scope="col">identidad</th>
                <th scope="col">Direccion</th>
                <th scope="col">Ciudad</th>
                <th scope="col">Telefono</th>
                <th scope="col">Pais</th>
                <th scope="col">Imagen</th>
                <th scope="col">Documentacion</th>
                <th scope="col">Creacion</th>
                <th scope="col">Descripcion</th>
                <th scope="col">Acciones</th>
    </thead>
    <tbody>
    <?php      
define('db_host', 'localhost');
define('db_username', 'root');
define('db_password', '');
define('db_dbname', 'sofware_erp');

// Conectar a MySQL y seleccionar la base de datos.
$mysqli = mysqli_connect(db_host, db_username, db_password, db_dbname);



// Verificar que la conexión sea exitosa
if (!$mysqli) {
    die('Error al conectarse a MySQL: ' . mysqli_connect_error());
}

// Establecer juego de caracteres UTF-8zvc c
mysqli_set_charset($mysqli, 'utf8');

// Consulta utilizando MySQLi
$consulta = "SELECT * FROM  empleados ORDER BY id_empleado";
$resultados = $mysqli->query($consulta);


// Comprobación de errores en la ejecución de la consulta
if (!$resultados) {
    die("Error al ejecutar la consulta: " . $mysqli->error);
}

// Iterar sobre los resultados y mostrarlos
while ($empleado= $resultados->fetch_assoc()) {
?>
    
    <tr>
    <td><?php echo htmlspecialchars($empleado['id_empleado']); ?></td>
        <td><?php echo htmlspecialchars($empleado['nombre_completo']); ?></td>
        <td><?php echo htmlspecialchars($empleado['cargo']); ?></td>
        <td><?php echo htmlspecialchars($empleado['fecha_contratacion']); ?></td>
        <td><?php echo htmlspecialchars($empleado['numero_horas']); ?></td>
        <td><?php echo htmlspecialchars($empleado['precio_hora']); ?></td>
        <td><?php echo htmlspecialchars($empleado['salario']); ?></td>
        <td><?php echo htmlspecialchars($empleado['estado']); ?></td>
        <td><?php echo htmlspecialchars($empleado['departamento']); ?></td>
        <td><?php echo htmlspecialchars($empleado['documento_identidad']); ?></td>
        <td><?php echo htmlspecialchars($empleado['direccion']); ?></td>
        <td><?php echo htmlspecialchars($empleado['ciudad']); ?></td>
        <td><?php echo htmlspecialchars($empleado['telefono']); ?></td>
        <td><?php echo htmlspecialchars($empleado['pais']); ?></td>
<td><img src="../../public/img/empleados/<?php echo $empleado['imagen']; ?>" width="100" alt=""></td>
<td><img src="../../public/img/empleados/<?php echo $empleado['documentacion_archivo']; ?>" width="100" alt=""></td>
<td><?php echo htmlspecialchars($empleado['fecha_creacion']); ?></td>
<td><?php echo htmlspecialchars($empleado['descripcion_profesional']); ?></td>
        <td>
              
                  <!-- Botón para editar -->  
                <a href="edit.php?da=3&lla=<?php echo $empleado['id_empleado']; ?>"  class="btn btn-custom-green btn-editar">
                <i class="fas fa-edit icon"></i> Editar

<!-- Botón de Borrar -->
<a href="#" class="btn btn-danger btn-borrar" onclick="borrarEmpleado(<?php echo $empleado['id_empleado']; ?>, '<?php echo $empleado['imagen']; ?>', '<?php echo $empleado['documentacion_archivo']; ?>')">
    <i class="fas fa-trash-alt"></i> Borrar
</a>

<script>
function borrarEmpleado(id, imagen, documentacionArchivo) {
    if (confirm('¿Está seguro de borrar el empleado?')) {
        // Realizar una petición AJAX para borrar el pedido
        var xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Éxito en la eliminación del pedido
                    alert('Empleado eliminado correctamente.');
                    // Recargar la página para reflejar los cambios
                    location.reload();
                } else {
                    // Error al eliminar el pedido
                    alert('Error al eliminar el empleado.');
                }
            }
        };

        // Configurar la URL de la petición AJAX
        var url = 'delete.php?lla=' + encodeURIComponent(id) + 
                  '&imagen=' + encodeURIComponent(imagen) + 
                  '&documentacion_archivo=' + encodeURIComponent(documentacionArchivo);

        // Configurar la petición AJAX
        xhr.open('GET', url, true);
        // Enviar la petición
        xhr.send();
    }
}
</script>


</tr>
            <?php
        }

        // Cerrar la conexión
        $mysqli->close();
        ?>
    </tbody>
</table>



</body>
</html>