<?php
$originalSql = file_get_contents('C:\xampp\htdocs\controlefinanceiro\database.sql');

$sql = "-- Criação do banco de dados\n";
$sql .= "CREATE DATABASE IF NOT EXISTS financeiro_vg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
$sql .= "USE financeiro_vg;\n\n";

$schema = preg_replace('/CREATE DATABASE.*?;/is', '', $originalSql);
$schema = preg_replace('/USE .*?;/is', '', $schema);
$sql .= $schema . "\n\n";

// Password hash for 123456
$password = password_hash('123456', PASSWORD_DEFAULT);

$sql .= "-- Inserindo o usuário\n";
$sql .= "INSERT INTO users (id, name, email, password, created_at, updated_at) VALUES (1, 'Fulano', 'fulano@gmail.com', '$password', '2025-08-01 00:00:00', '2025-08-01 00:00:00');\n\n";

// Let's create categories
$categories = [
    [1, 'Alimentação', 'expense', '#FF5252', 'fas fa-utensils'],
    [2, 'Transporte', 'expense', '#448AFF', 'fas fa-car'],
    [3, 'Saúde', 'expense', '#FF4081', 'fas fa-heartbeat'],
    [4, 'Educação', 'expense', '#7C4DFF', 'fas fa-book'],
    [5, 'Lazer', 'expense', '#FFC107', 'fas fa-ticket-alt'],
    [6, 'Moradia', 'expense', '#795548', 'fas fa-home'],
    [7, 'Contas', 'expense', '#607D8B', 'fas fa-file-invoice-dollar'],
    [8, 'Compras', 'expense', '#E040FB', 'fas fa-shopping-bag'],
    [9, 'Roupas', 'expense', '#00BCD4', 'fas fa-tshirt'],
    [10, 'Salário', 'income', '#4CAF50', 'fas fa-money-bill-wave'],
    [11, 'Investimentos', 'income', '#8BC34A', 'fas fa-chart-line'],
    [12, 'Freelance', 'income', '#CDDC39', 'fas fa-laptop-code'],
    [13, 'Outros', 'expense', '#9E9E9E', 'fas fa-tag']
];

$sql .= "-- Inserindo categorias\n";
foreach ($categories as $c) {
    $sql .= "INSERT INTO categories (id, user_id, name, type, color, icon, is_default) VALUES ({$c[0]}, 1, '{$c[1]}', '{$c[2]}', '{$c[3]}', '{$c[4]}', 1);\n";
}
$sql .= "\n";

// Create accounts
$sql .= "-- Inserindo contas\n";
$sql .= "INSERT INTO accounts (id, user_id, name, type, balance, credit_limit, closing_day, due_day, color) VALUES 
(1, 1, 'Nubank', 'credit_card', 0.00, 5000.00, 25, 5, '#8A05BE'),
(2, 1, 'Itaú', 'bank_account', 12500.50, NULL, NULL, NULL, '#FF7A00'),
(3, 1, 'C6 Bank', 'credit_card', 0.00, 3000.00, 10, 20, '#242424'),
(4, 1, 'Dinheiro Físico', 'cash', 250.00, NULL, NULL, NULL, '#4CAF50'),
(5, 1, 'VR Benefícios', 'meal_voucher', 800.00, NULL, NULL, NULL, '#E91E63');\n\n";

// Insert reserves
$sql .= "-- Inserindo reservas\n";
$sql .= "INSERT INTO reserves (id, user_id, account_id, name, description, target_amount, current_amount, deadline, priority, status, color) VALUES 
(1, 1, 2, 'Viagem para Europa', 'Férias em 2027', 20000.00, 8500.00, '2027-01-15', 'high', 'active', '#2196F3'),
(2, 1, 2, 'Troca de Carro', 'Comprar carro novo', 50000.00, 15000.00, '2028-06-01', 'medium', 'active', '#FF9800'),
(3, 1, 2, 'Reserva de Emergência', '6 meses de custo de vida', 30000.00, 25000.00, NULL, 'high', 'active', '#4CAF50');\n\n";

// Insert reserve transactions
$sql .= "INSERT INTO reserve_transactions (reserve_id, user_id, amount, type, description, transaction_date) VALUES \n";
for($i=1; $i<=12; $i++) {
    $date = date('Y-m-d', strtotime("2025-08-01 +$i months"));
    $sql .= "(1, 1, 500.00, 'deposit', 'Depósito mensal', '$date'),\n";
    $sql .= "(2, 1, 1000.00, 'deposit', 'Depósito mensal', '$date'),\n";
    $sql .= "(3, 1, 1500.00, 'deposit', 'Depósito mensal', '$date')" . ($i==12 ? ";" : ",") . "\n";
}
$sql .= "\n";


// Insert transactions
$sql .= "-- Inserindo transações (Agosto 2025 a Agosto 2026)\n";
$transactions = [];
$tid = 1;
for ($m = 0; $m <= 12; $m++) {
    $year = 2025;
    $month = 8 + $m;
    if ($month > 12) {
        $year++;
        $month -= 12;
    }
    $monthStr = str_pad($month, 2, '0', STR_PAD_LEFT);
    
    // Salário
    $transactions[] = "($tid, 1, 2, 10, 'income', 8500.00, 'Salário Mensal', '$year-$monthStr-05', 'paid')"; $tid++;
    // Freelance (random)
    if (rand(0, 1)) {
        $transactions[] = "($tid, 1, 2, 12, 'income', " . rand(500, 2000) . ".00, 'Projeto Freelance', '$year-$monthStr-15', 'paid')"; $tid++;
    }

    // Fixed expenses
    $transactions[] = "($tid, 1, 2, 6, 'expense', 2500.00, 'Aluguel', '$year-$monthStr-10', 'paid')"; $tid++;
    $transactions[] = "($tid, 1, 2, 7, 'expense', 150.00, 'Internet', '$year-$monthStr-15', 'paid')"; $tid++;
    $transactions[] = "($tid, 1, 2, 7, 'expense', 220.00, 'Energia Elétrica', '$year-$monthStr-15', 'paid')"; $tid++;
    $transactions[] = "($tid, 1, 1, 4, 'expense', 850.00, 'Faculdade', '$year-$monthStr-05', 'paid')"; $tid++;

    // Credit card (Nubank - ID 1)
    for($i = 0; $i < rand(5, 15); $i++) {
        $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $amount = rand(20, 300) + (rand(0,99)/100);
        $cat = [1, 2, 3, 5, 8, 9][array_rand([1, 2, 3, 5, 8, 9])];
        $desc = ['Uber', 'Ifood', 'Farmácia', 'Cinema', 'Mercado', 'Amazon', 'Mercado Livre'][array_rand(['Uber', 'Ifood', 'Farmácia', 'Cinema', 'Mercado', 'Amazon', 'Mercado Livre'])];
        $transactions[] = "($tid, 1, 1, $cat, 'expense', $amount, '$desc', '$year-$monthStr-$day', 'paid')"; $tid++;
    }

    // VR Benefícios (ID 5)
    for($i = 0; $i < rand(3, 8); $i++) {
        $day = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $amount = rand(30, 150) + (rand(0,99)/100);
        $transactions[] = "($tid, 1, 5, 1, 'expense', $amount, 'Restaurante', '$year-$monthStr-$day', 'paid')"; $tid++;
    }
}

$chunks = array_chunk($transactions, 100);
foreach ($chunks as $chunk) {
    $sql .= "INSERT INTO transactions (id, user_id, account_id, category_id, type, amount, description, transaction_date, status) VALUES \n";
    $sql .= implode(",\n", $chunk) . ";\n";
}

file_put_contents('C:\xampp\htdocs\controlefinanceiro\database.sql', $sql);
echo "SQL database.sql replaced successfully.";
