<html >
<head>
	<meta charset="utf-8" />
	<script src="Chart.js"></script>
	<?php
		$servername = "localhost";
		$username = "root";
		$password = "";

		try {
			$conn = new PDO("mysql:host=$servername;dbname=movilesbluetooth", $username, $password);
			// set the PDO error mode to exception
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			//echo "Connected successfully <br>";
			}
		catch(PDOException $e)
			{
			echo "Connection failed: " . $e->getMessage();
			}
	?>	
</head>
<body>
<h1>Curso <?php echo $_GET['curso']?></h1>

<h2>Presentismo:</h2><br>
<canvas id="myChart" width="400" height="300"></canvas>


<?php $sql = "SELECT COUNT(*) as cant, estado FROM asistencia
			INNER JOIN estado_asistencia ON asistencia.estado_asistencia_id=estado_asistencia.id
			INNER JOIN clase on asistencia.clase_id=clase.id
			INNER JOIN curso on clase.curso_id=curso.id
			WHERE curso_id = ".$_GET['curso']." GROUP BY estado_asistencia_id";
	$result = $conn->query($sql);
	
	
?>
<script>
var data = [
    
	<?php while($row = $result->fetch()) { ?>
	{
        value: <?php echo $row["cant"]; ?>,
        color:getRandomColor(),
        highlight: "#F0F0FE",
        label:" <?php echo $row["estado"]; ?> "
    },
	<?php } ?>
]

var options = {
	 animateRotate : true,
    animateScale : true,
	showScale: true,
	scaleShowLabels: true
	
}
var ctx = document.getElementById("myChart").getContext("2d");
var myPieChart = new Chart(ctx).Doughnut(data,options);

function getRandomColor() {
    var letters = '0123456789ABCDEF'.split('');
    var color = '#';
    for (var i = 0; i < 6; i++ ) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}
</script>

<h2>Usuarios:</h2>
<?php	$sql = "SELECT * FROM usuario
			INNER JOIN curso_usuario ON curso_usuario.usuario_id=usuario.id
			WHERE curso_id = ".$_GET['curso'];
	$result = $conn->query($sql);
	
	if ($result) {
		while($row = $result->fetch()) {
			echo "<ul>";
			echo "<li> id: " . $row["id"]. " - Nombre: " . $row["nombre"]. " " . $row["apellido"]. "</li> ";
			echo "</ul>";
		}
	} else {
		echo "0 results";
	}
	

?> 

<h2>Presentes por usuario:</h2>
<canvas id="usuarios" width="400" height="300"></canvas>
<?php
$sql = "SELECT COUNT(*) as cant, estado, usuario.nombre, apellido FROM asistencia
			INNER JOIN estado_asistencia ON asistencia.estado_asistencia_id=estado_asistencia.id
			INNER JOIN clase on asistencia.clase_id=clase.id
			INNER JOIN curso on clase.curso_id=curso.id
			INNER JOIN usuario on asistencia.usuario_id=usuario.id
			WHERE curso_id = ".$_GET['curso']." AND estado_asistencia_id=2
			GROUP BY usuario.id";
	$result = $conn->query($sql);
	?>
	
<script>
var data = [
    
	<?php while($row = $result->fetch()) { ?>
	{
        data: [<?php echo $row["cant"]; ?>],
        color:getRandomColor(),
        highlight: "#F0F0FE",
        label:" <?php echo $row["nombre"]." ".$row['apellido']; ?> "
    },
	<?php } ?>
]

var options = {
	 animateRotate : true,
    animateScale : true,
	showScale: true,
	scaleShowLabels: true
	
}
var ctx = document.getElementById("usuarios").getContext("2d");
var myPieChart = new Chart(ctx).Bar(data);



</body>
</html>








