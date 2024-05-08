<?php
ob_start();

if ($data = file_get_contents('php://input')) {
	$info = json_encode($data);
}




$info = str_replace('"', '', $info);
$info = str_replace('{', '', $info);
$info = str_replace('}', '', $info);
$info = str_replace('email:', '', $info);
$info = str_replace('password:', '', $info);
$info = str_replace(' ', '', $info);
$info = str_replace(':', '', $info);
$info = str_replace(',', '', $info);
$info = explode('\\', $info);




include('../sistema_global/conexion.php');
$email = clear($info[3]);
$contrasena = clear($info[7]);
$email = mysqli_real_escape_string($conexion,  $email);
$contrasena = mysqli_real_escape_string($conexion,  $contrasena);


$stmt = mysqli_prepare($conexion, "SELECT * FROM `system_users` WHERE u_email = ? AND u_contrasena!='' LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {

	while ($row = $result->fetch_assoc()) {
		if (password_verify($contrasena, $row['u_contrasena'])) {

			session_start();
			$_SESSION['u_nombre'] = $row['u_nombre'];
			$_SESSION['u_oficina_id'] = $row['u_oficina_id'];
			$_SESSION['u_oficina'] = $row['u_oficina'];

			// regresa una respuesta al fetch
			$folder = '';
			switch ($row['u_oficina_id']) {
				case '1':
					$folder = 'mod_nomina';
					break;
				case '2':
					$folder = 'mod_compras';
					break;
				case '3':
					$folder = 'mod_administracion';
					break;
				case '4':
					$folder = 'mod_planificacion';
					break;
				case '5':
					$folder = 'mod_tesoreria';
					break;
			}
			echo json_encode(array('of' => $folder, 'val' => true));

			/*header('Location: ../front/' . $folder . '/index');*/
		} else {
			// regresa al index en la carpeta anterior y pasele un mensaje por post
			echo json_encode(array('of' => 0, 'val' => false));
		}
	}
} else {
	echo json_encode(array('of' => 0, 'val' => false));
}
$stmt->close();


ob_end_flush();
