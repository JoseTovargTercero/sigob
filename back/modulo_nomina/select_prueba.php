<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtro de Select</title>
    <style>
        #filterContainer {
            display: none;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div id="filterContainer">
        <input type="text" id="filterInput" placeholder="Escribe para filtrar...">
    </div>
    <select id="mySelect" size="10">
        <!-- Opciones de ejemplo -->
        <option value="1">Option 1</option>
        <option value="2">Option 2</option>
        <option value="3">Option 3</option>
        <option value="4">Option 4</option>
        <option value="5">Option 5</option>
        <!-- Agrega aquí más opciones según sea necesario -->
    </select>
<script>
        document.addEventListener('DOMContentLoaded', function() {
    const filterContainer = document.getElementById('filterContainer');
    const filterInput = document.getElementById('filterInput');
    const mySelect = document.getElementById('mySelect');

    mySelect.addEventListener('focus', function() {
        filterContainer.style.display = 'block';
        filterInput.focus();
    });

    mySelect.addEventListener('blur', function() {
        setTimeout(() => {
            filterContainer.style.display = 'none';
        }, 10000); // Retraso para permitir la selección de opciones
    });

    filterInput.addEventListener('input', function() {
        const filter = filterInput.value.toLowerCase();
        const options = mySelect.options;

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            const optionText = option.text.toLowerCase();

            if (optionText.includes(filter)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
    });

    filterInput.addEventListener('blur', function() {
        setTimeout(() => {
            filterContainer.style.display = 'none';
        }, 10000); // Retraso para permitir la selección de opciones
    });
});
    </script>
</body>
</html>
