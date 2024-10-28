<?php



class DatabaseHandler
{
    private $conexion;

    public function __construct($conexion)
    {
        $this->conexion = $conexion;

        // Verificar si la conexión es exitosa
        if ($this->conexion->connect_error) {
            throw new Exception("Error de conexión a la base de datos: " . $this->conexion->connect_error);
        }
    }


    /**
     * Realizar una consulta SELECT en la base de datos.
     * 
     * Este método permite realizar consultas a una tabla específica, 
     * seleccionando las columnas deseadas, aplicando condiciones, 
     * ordenando los resultados y realizando uniones (INNER JOIN) 
     * con otras tablas si es necesario. Los resultados se devuelven 
     * en formato JSON con una clave 'success' que contiene los datos obtenidos.
     *
     * @param array $columnas Columnas a seleccionar en la consulta. 
     *                               Si se omite, se seleccionan todas las columnas.
     * @param string $nombre_tabla Nombre de la tabla de la cual se desea realizar la consulta.
     * @param string $condicion Condición para filtrar los registros (ej. 'id = 1'). 
     *                          Por defecto es una cadena vacía, lo que significa que no hay filtrado.
     * @param array $order_by Arreglo que especifica cómo se deben ordenar los resultados.
     *                        Cada elemento debe ser un array asociativo con 'campo' y 'order' (ASC o DESC).
     * Formato
     *      ['campo' => 'id', 'order' => 'ASC'],
     *      ['campo' => 'actividad', 'order' => 'DESC']
     * 
     * @param array $join Arreglo que especifica las uniones con otras tablas. 
     *                    Cada elemento debe ser un array asociativo con la tabla y su condición de unión.
     * Formato
     *      'tabla_join' => 'entes.id = tabla_join.id',
     *      'pl_programas' => 'entes.programa = pl_programas.programa'
     * 
     * @throws Exception Si ocurre un error al preparar, ejecutar la consulta o al obtener los resultados.
     * @return string Resultado de la consulta en formato JSON, incluyendo un array 'success' 
     *                con los registros obtenidos.
     * 
     *
     */



    public function select($columnas = ['*'], $nombre_tabla, $condicion = "", $order_by = [], $join = [])
    {
        $data = [];

        // Generar lista de columnas para SELECT
        $campos = is_array($columnas) ? implode(", ", $columnas) : "*";

        // Generar consulta base
        $query = "SELECT $campos FROM `$nombre_tabla`";

        // Agregar INNER JOIN si está presente
        if (!empty($join)) {
            foreach ($join as $tabla => $condicion_join) {
                $query .= " INNER JOIN $tabla ON $condicion_join";
            }
        }

        // Agregar condición si está presente
        if (!empty($condicion)) {
            $query .= " WHERE $condicion";
        }

        // Agregar orden si está presente
        if (!empty($order_by)) {
            $order_clauses = [];
            foreach ($order_by as $order) {
                if (isset($order['campo'], $order['order'])) {
                    $order_clauses[] = $order['campo'] . " " . strtoupper($order['order']);
                }
            }
            if (!empty($order_clauses)) {
                $query .= " ORDER BY " . implode(", ", $order_clauses);
            }
        }

        // Preparar 
        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $this->conexion->error);
        }

        // Ejecutar 
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        // Obtener resultados
        $result = $stmt->get_result();
        if ($result === false) {
            throw new Exception("Error al obtener los resultados: " . $stmt->error);
        }

        // Almacenar resultados en un array
        while ($row = $result->fetch_assoc()) {
            array_push($data, $row);
        }

        $stmt->close();


        return json_encode(['success' => $data]);
    }











 /// TODO: PENDIENTE
    public function insert($tabla, $campos_valores)
    {
        // Validación y preparación de campos y placeholders
        $campos = [];
        $placeholders = [];
        $tipos = '';
        $valores = [];
        $condicionUnica = '';

        foreach ($campos_valores as $item) {
            // Expandir cada elemento a un array de 4 elementos, rellenando con null si faltan
            list($campo, $valor, $tipo, $unico) = array_pad($item, 4, null);

            $campos[] = $campo;
            $placeholders[] = '?';
            $tipos .= $tipo;
            $valores[] = $valor;

            // Si $unico es true, agregamos la condición a la cadena $condicionUnica
            if ($unico === true) {
                $condicionUnica .= ($condicionUnica ? " AND " : "") . "$campo = '$valor'";
            }
        }


        if (!empty($condicionUnica)) {
            $valoresUnicos = [
                ['tabla' => $tabla, 'condicion' => $condicionUnica]
            ];
            // Ejecutar comprobación de existencia
            $totalCoincidencias = $this->comprobar_existencia($valoresUnicos);

            // Si hay coincidencias, retornar mensaje de error sin ejecutar la inserción
            if ($totalCoincidencias > 0) {
                throw new Exception("El registro ya existe en la tabla.");
            }
        }

        // quedaste aca, valida que registre y mejora la comprovacion y select para que use stmt



        // Preparar la consulta SQL de inserción
        $query = "INSERT INTO `$tabla` (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception("Error al preparar la consulta INSERT: " . $this->conexion->error);
        }

        // Asignar tipos y valores a la consulta preparada
        $stmt->bind_param($tipos, ...$valores);

        // Ejecutar la consulta de inserción
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Error al ejecutar la consulta INSERT: " . $stmt->error);
        }

        // Obtener el número de filas afectadas
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        // Registrar la acción
        $this->logAction('INSERT', $tabla, json_encode($campos_valores), $affected_rows);

        return [
            'success' => true,
            'affected_rows' => $affected_rows
        ];
    }




















    /**
     * Eliminar registros de una tabla en la base de datos.
     * 
     * Este método elimina registros de la tabla especificada, utilizando la 
     * condición proporcionada. Si la condición no es especificada, se lanza 
     * una excepción para evitar la eliminación accidental de todos los registros.
     * Además, registra la acción en el log de auditoría, indicando cuántas 
     * filas fueron afectadas.
     *
     * @param string $nombre_tabla Nombre de la tabla de la cual se eliminarán los registros.
     * @param string $condicion Condición que especifica qué registros se deben eliminar (ej. 'id = 1').
     * @throws Exception Si la condición está vacía o si ocurre un error al preparar o ejecutar la consulta.
     * @return array Resultado de la operación, incluyendo un indicador de éxito y el número de filas afectadas.
     */
    public function delete($nombre_tabla, $condicion)
    {
        if (empty($condicion)) {
            throw new Exception("No se ha recibido la información requerida.");
        }

        $query = "DELETE FROM `$nombre_tabla` WHERE $condicion";
        $stmt = $this->conexion->prepare($query);

        // Verificar si la preparación fue exitosa
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta DELETE: " . $this->conexion->error);
        }

        // Ejecutar
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Error al ejecutar la consulta DELETE: " . $stmt->error);
        }

        // Cerrar la sentencia y registrar la acción
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        // Registrar la acción
        $this->logAction('DELETE', $nombre_tabla, $condicion, $affected_rows);

        return [
            'success' => true,
            'affected_rows' => $affected_rows
        ];
    }




    /**
     * Actualizar registros en la base de datos.
     * 
     * Este método permite actualizar uno o varios campos de registros en una tabla específica. 
     *
     * @param string $nombre_tabla Nombre de la tabla donde se realizarán las actualizaciones.
     * @param array $valores Array con los campos, valores y tipos a actualizar en el formato [$campo, $valor, $tipo].
     * @param string $where Condición que indica qué registros se deben actualizar (ej. 'id = 1').
     * @throws Exception Si ocurre un error al preparar, ejecutar la consulta o al registrar la acción.
     * @return array Resultado de la operación, indicando si fue exitosa y cuántas filas fueron afectadas.
     */
    public function update($nombre_tabla, $valores, $where)
    {
        // Verificar si se recibieron valores y la condición WHERE
        if (empty($valores) || empty($where)) {
            throw new Exception("Se requieren valores para actualizar y una condición WHERE.");
        }

        // Generar la parte SET de la consulta
        $set_clause = [];
        $param_types = ""; // Inicializar la cadena de tipos

        foreach ($valores as $item) {
            if (count($item) !== 3) {
                throw new Exception("Cada elemento del array de valores debe contener el campo, valor y tipo.");
            }

            $campo = $item[0];
            $valor = $item[1];
            $tipo = $item[2];

            $set_clause[] = "$campo = ?";
            $param_types .= $tipo; // Agregar el tipo a la cadena de tipos
            $params[] = $valor; // Agregar el valor al array de parámetros
        }

        $set_clause_string = implode(", ", $set_clause);

        // Preparar la consulta SQL
        $query = "UPDATE `$nombre_tabla` SET $set_clause_string WHERE $where";
        $stmt = $this->conexion->prepare($query);

        // Verificar si la preparación fue exitosa
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta UPDATE: " . $this->conexion->error);
        }

        // Vincular los parámetros
        $stmt->bind_param($param_types, ...$params);

        // Ejecutar la consulta
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Error al ejecutar la consulta UPDATE: " . $stmt->error);
        }

        // Cerrar la sentencia y registrar la acción
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        // Registrar la acción
        $this->logAction('UPDATE', $nombre_tabla, $where, $affected_rows);

        return [
            'success' => true,
            'affected_rows' => $affected_rows
        ];
    }





    /**
     * Almacenar las acciones realizada por el usuario.
     * 
     * Este método inserta un registro en la tabla de auditoría con la información 
     * sobre la acción realizada, incluyendo el tipo de acción, la tabla afectada, 
     * la condición bajo la cual se realizó la acción y la cantidad de filas afectadas. 
     * También almacena el ID del usuario que realizó la acción.
     *
     * @param string $action_type Tipo de acción realizada (ej. 'INSERT', 'DELETE', 'UPDATE').
     * @param string $table_name Nombre de la tabla afectada por la acción.
     * @param string $condition Condición que describe la acción (ej. 'id = 1').
     * @param int $affected_rows Número de filas afectadas por la acción.
     * @throws Exception Si ocurre un error al preparar o ejecutar la consulta.
     */
    public function logAction($action_type, $table_name, $condition, $affected_rows)
    {
        $user_id = $_SESSION['u_id'];
        $query = "INSERT INTO audit_logs (action_type, table_name, situation, affected_rows, user_id) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($query);

        if (!$stmt) {
            throw new Exception("Error al preparar la consulta para registrar acción: " . $this->conexion->error);
        }

        $stmt->bind_param('sssis', $action_type, $table_name, $condition, $affected_rows, $user_id);

        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("Error al registrar la acción: " . $stmt->error);
        }
        $stmt->close();
    }





    /**
     * Comprobar la existencia de registros en múltiples tablas.
     * 
     * @param array $tablas tablas con sus condiciones a verificar.
     * @return int Número total de coincidencias encontradas.
     */
    public function comprobar_existencia(array $tablas)
    {
        $totalCoincidencias = 0;

        foreach ($tablas as $tabla) {
            if (isset($tabla['tabla']) && isset($tabla['condicion'])) {
                $resultado = json_decode($this->select(null, $tabla['tabla'], $tabla['condicion'], null, null));

                if (isset($resultado->success) && is_array($resultado->success)) {
                    $totalCoincidencias += count($resultado->success);
                }
            } else {
                throw new Exception("Cada entrada debe contener 'tabla' y 'condicion'.");
            }
        }

        return $totalCoincidencias;
    }
}
