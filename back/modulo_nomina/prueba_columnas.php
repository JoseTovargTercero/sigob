<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Consulta</title>
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
    <h1>Consulta de Valores Distintos</h1>
    <form id="consultaForm">
        <label for="columna">Nombre de la columna:</label>
        <input type="text" id="columna" name="columna" required>
        <button type="submit">Consultar</button>
    </form>
    <div class="result" id="result"></div>

    <script>
        document.getElementById('consultaForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const columna = document.getElementById('columna').value;

            fetch('nom_columnas_return.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ columna: columna })
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('result');
                if (data.error) {
                    resultDiv.innerHTML = `<p style="color: red;">Error: ${data.error}</p>`;
                } else {
                    resultDiv.innerHTML = `<p>Valores distintos:</p><ul>${data.map(value => `<li>${value}</li>`).join('')}</ul>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
    </script>
</body>
</html>
