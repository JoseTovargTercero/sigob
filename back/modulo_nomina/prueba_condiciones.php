<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Condiciones SQL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 50px;
        }
        form {
            margin-bottom: 20px;
        }
        .result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Consulta de Condiciones SQL</h1>
    <form id="consultaForm">
        <label for="condicion">Condición SQL:</label>
        <input type="text" id="condicion" name="condicion" required>
        <button type="submit">Consultar</button>
    </form>
    <div class="result" id="result"></div>

    <script>
        document.getElementById('consultaForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const condicion = document.getElementById('condicion').value;

            fetch('nom_condiciones_return.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ condicion: condicion })
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                if (data === 'PROHIBIDO') {
                    resultDiv.innerHTML = `<p style="color: red;">PROHIBIDO: La condición contiene palabras clave no permitidas.</p>`;
                } else if (data === 'error') {
                    resultDiv.innerHTML = `<p style="color: red;">Error al ejecutar la consulta.</p>`;
                } else {
                    resultDiv.innerHTML = `<p>Coincidencias encontradas: ${data}</p>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
