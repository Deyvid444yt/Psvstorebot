<?php
// Token do seu bot do Telegram
$token = '6521051141:AAEhBYpmarKOdkPoQe9IGMU_zLb8WhXaQ5E';

// URL da API do Telegram
$apiUrl = "https://api.telegram.org/bot$token";

// Obtenha os dados da mensagem recebida
$update = file_get_contents("php://input");
$update = json_decode($update, true);

// Verifique se a mensagem é um comando /psv
if (isset($update['message']['text']) && strpos($update['message']['text'], '/psv') === 0) {
    // Obtenha o termo de pesquisa do comando
    $searchTerm = trim(str_replace('/psv', '', $update['message']['text']));

    // Faça uma solicitação HTTP para o site para obter o JSON
    $jsonUrl = "http://psvstore.000.pe/Planilha.php";
    $jsonData = @file_get_contents($jsonUrl);

    if ($jsonData !== false) {
        $data = json_decode($jsonData, true);

        if ($data !== null) {
            $results = array();

            // Procure todos os jogos com nomes correspondentes
            foreach ($data as $game) {
                if (isset($game['nome']) && stripos($game['nome'], $searchTerm) !== false) {
                    $results[] = $game;
                }
            }

            if (!empty($results)) {
                // Construa a resposta com os links necessários
                $response = "Resultados encontrados:\n";

                foreach ($results as $result) {
                    $response .= $result['nome'] . "\n";
                    $response .= "Download Pkg: " . $result['game'] . "\n";
                    $response .= "Download WORK: " . $result['work'] . "\n";
                    $response .= "-----------\n";
                }
            } else {
                // Se não encontrar jogos, use uma mensagem de erro
                $response = "Nenhum jogo encontrado para: $searchTerm";
            }
        } else {
            // JSON inválido
            $response = "Desculpe, houve um problema na obtenção dos dados. Tente novamente mais tarde.";
        }
    } else {
        // Problema na obtenção do JSON
        $response = "Desculpe, houve um problema na obtenção dos dados. Tente novamente mais tarde.";
    }
} elseif (isset($update['message']['text']) && strpos($update['message']['text'], '/addgrupo') === 0) {
    // Obtenha o link do grupo do comando
    $groupLink = trim(str_replace('/addgrupo', '', $update['message']['text']));

    // Use a API do Telegram para ingressar no grupo
    $response = joinGroup($groupLink, $apiUrl);
} else {
    // Se a mensagem não for um comando /psv ou /addgrupo, envie a mensagem de erro padrão
    $response = "Desculpe, esse bot só responde a comandos /psv e /addgrupo.";
}

// Obtenha o chat_id do usuário
$chatId = $update['message']['chat']['id'] ?? null;

if (!empty($chatId)) {
    // Envie a resposta de volta para o usuário
    $sendMessageUrl = $apiUrl . "/sendMessage?chat_id=$chatId&text=" . urlencode($response);
    file_get_contents($sendMessageUrl);
}

function joinGroup($groupLink, $apiUrl) {
    // Use a API do Telegram para ingressar no grupo
    $response = "Tentando ingressar no grupo...";

    // Modifique o link do grupo corretamente
    if (strpos($groupLink, 'https://t.me/') === 0) {
        $groupLink = str_replace('https://t.me/', '', $groupLink);
    }

    $chatId = null; // Defina o chat_id do grupo

    if (!empty($chatId)) {
        $inviteUrl = $apiUrl . "/inviteChat?chat_id=$chatId&invite_link=$groupLink";
        $result = file_get_contents($inviteUrl);

        if ($result === 'true') {
            $response = "Você foi adicionado com sucesso ao grupo!";
        } else {
            $response = "Desculpe, não foi possível adicionar você ao grupo.";
        }
    } else {
        $response = "Desculpe, não foi possível encontrar o grupo.";
    }

    return $response;
}
?>
