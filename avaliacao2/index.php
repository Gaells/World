<?php
// Conectar ao banco de dados
$servername = "200.236.3.126";
$username = "root";
$password = "example";
$database = "world";

$conn = new mysqli($servername, $username, $password, $database);

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Defina o número máximo de itens por página
$itensPorPagina = isset($_GET['itens_por_pagina']) ? $_GET['itens_por_pagina'] : 10;

// Paginação
$paginaAtual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;
$inicio = ($paginaAtual - 1) * $itensPorPagina;

// Ordenação
$campoOrdenacao = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'CountryName';
$ordenacao = in_array($campoOrdenacao, ['CountryName', 'Capital', 'Population']) ? $campoOrdenacao : 'CountryName';

// Busca
$termoBusca = isset($_GET['busca']) ? $_GET['busca'] : '';

// Consulta SQL base
$sqlBase = "SELECT c.Name AS CountryName, ci.Name AS Capital, c.Population, GROUP_CONCAT(l.Language SEPARATOR ', ') as Languages
            FROM Country c
            LEFT JOIN City ci ON c.Capital = ci.ID
            LEFT JOIN CountryLanguage l ON c.Code = l.CountryCode";

// Adicionar cláusulas conforme necessário
$sql = $sqlBase;

if (!empty($termoBusca)) {
    $sql .= " WHERE c.Name LIKE '%$termoBusca%' OR ci.Name LIKE '%$termoBusca%'";
}

$sql .= " GROUP BY c.Code ORDER BY $ordenacao LIMIT $inicio, $itensPorPagina";

$result = $conn->query($sql);

// Fechar a conexão
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Países</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');
        body {
        background-color: #f2f2f2;
        color: #333;
        font-family: 'Roboto', sans-serif;
    }
        form {
        margin-bottom: 20px;
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
    }

    label {
        display: block;
        margin-bottom: 5px;
        color: #666;
    }

    table {
        background-color: #e6e6e6;
        border-collapse: collapse;
        width: 100%;
        margin-top: 5px;
    }

    input, select {
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 200px;
        margin: 10px;
    }

    th, td {
        border: 1px solid #ccc;
        text-align: left;
        padding: 10px;
        color: #292370;
    }

    button {
        background-color: #4CAF50;
        color: #fff;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        width: 80px;
        height: 33px;
        margin-left: 8px;
    }

    button:hover {
        background-color: #45a049;
    }

    </style>
</head>
<body>

    <h2>Lista de Países</h2>

    <form method="GET">
        <label for="itens_por_pagina"> Itens por Página: </label>
        <input type="number" name="itens_por_pagina" value="<?php echo $itensPorPagina; ?>" min="1" max="1000">
        <label for="ordenar_por"> Ordenar por: </label>
        <select name="ordenar_por">
            <option value="CountryName" <?php echo ($ordenacao == 'CountryName') ? 'selected' : ''; ?>>Nome</option>
            <option value="Capital" <?php echo ($ordenacao == 'Capital') ? 'selected' : ''; ?>>Capital</option>
            <option value="Population" <?php echo ($ordenacao == 'Population') ? 'selected' : ''; ?>>População</option>
        </select>
        <label for="busca"> Buscar: </label>
        <input type="text" name="busca" value="<?php echo $termoBusca; ?>">
        <button type="submit">Aplicar</button>
    </form>

    <table>
        <tr>
            <th>Nome do País</th>
            <th>Capital</th>
            <th>População Total</th>
            <th>Línguas Faladas</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["CountryName"] . "</td>";
                echo "<td>" . $row["Capital"] . "</td>";
                echo "<td>" . $row["Population"] . "</td>";
                echo "<td>" . $row["Languages"] . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Nenhum resultado encontrado.</td></tr>";
        }
        ?>
    </table>

</body>
</html>
