<?php
ob_start();
include '../sistema_global/conexion.php';
require_once '../sistema_global/tasa_funciones.php';

$values = json_decode(file_get_contents('php://input'), true);

$email = clear($values['email']);
$contrasena = clear($values['password']);


$email = mysqli_real_escape_string($conexion, $email);
$contrasena = mysqli_real_escape_string($conexion, $contrasena);


$stmt = mysqli_prepare($conexion, "SELECT * FROM `system_users` WHERE u_email = ? AND u_contrasena!='' AND u_status='1' LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {

	while ($row = $result->fetch_assoc()) {
		if (password_verify($contrasena, $row['u_contrasena'])) {

			session_start();
			$_SESSION['u_id'] = $row['u_id'];
			$_SESSION['u_nombre'] = $row['u_nombre'];
			$_SESSION['u_oficina_id'] = $row['u_oficina_id'];
			$_SESSION['u_oficina'] = $row['u_oficina'];
			$_SESSION['u_nivel'] = $row['u_nivel'];
			$_SESSION['verificar_upload'] = false;


			if (tasaIsEmpty($conexion)) {
				$tasa = obtenerTasaDeApi(); // Función para obtener la tasa de la API o 0 en caso de error
				guardarTasa($conexion, $tasa);
			} else {
				if (verificarUltimaActualizacionTasa($conexion)) {
					cambiarTasa($conexion);
				}
				// Si la tasa no está vacía, actualizar
			}

			$id = $row['u_id'];

			if ($row['u_nivel'] != 1) {
				$permisos = [];

				$stmt_2 = mysqli_prepare($conexion, "SELECT sup.id_item_menu, menu.dir FROM `system_users_permisos` AS sup 
				LEFT JOIN menu ON menu.id = sup.id_item_menu
				WHERE id_user = ?");
				$stmt_2->bind_param('i', $id);
				$stmt_2->execute();
				$result = $stmt_2->get_result();
				if ($result->num_rows > 0) {
					while ($row_p = $result->fetch_assoc()) {
						$permisos[$row_p['id_item_menu']] = $row_p['dir'];
					}
				}
				$stmt_2->close();
				$_SESSION['permisos'] = $permisos;
			}

			// regresa una respuesta al fetch
			$folder = '';
			switch ($row['u_oficina_id']) {
				case '1':
					$folder = 'mod_nomina';
					$_SESSION['verificar_upload'] = true;
					break;
				case '2':
					$folder = 'mod_registro_control';
					break;
				case '3':
					$folder = 'mod_relaciones_laborales';
					break;
				case '4':
					$folder = 'mod_pl_formulacion';
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
