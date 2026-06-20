<?php

return [
    'openapi' => '3.0.3',
    'info' => [
        'title' => config('app.name', 'Caritas System').' API',
        'version' => '1.0.0',
        'description' => 'API para autenticação, estoque e clientes do bazar, paróquias, famílias e usuários administrativos.',
    ],
    'servers' => [
        [
            'url' => rtrim((string) config('app.url'), '/').'/api',
            'description' => 'API',
        ],
    ],
    'tags' => [
        ['name' => 'Autenticação'],
        ['name' => 'Bazar'],
        ['name' => 'Caixas'],
        ['name' => 'Estoque Paroquiais'],
        ['name' => 'Paróquias'],
        ['name' => 'Famílias'],
        ['name' => 'Visitas Domiciliares'],
        ['name' => 'Usuários'],
    ],
    'paths' => [
        '/diocese/login' => [
            'post' => [
                'tags' => ['Autenticação'],
                'summary' => 'Login como admin da diocese',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/LoginRequest'],
                            'example' => [
                                'email' => 'test@example.com',
                                'password' => 'password',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['$ref' => '#/components/responses/LoginSuccess'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/parish/login' => [
            'post' => [
                'tags' => ['Autenticação'],
                'summary' => 'Login em uma paróquia',
                'description' => 'Informe parish_id ou parish_slug junto com email e senha.',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/ParishLoginRequest'],
                            'example' => [
                                'email' => 'admin@paroquia.com',
                                'password' => 'password',
                                'parish_id' => 1,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => ['$ref' => '#/components/responses/LoginSuccess'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/me' => [
            'get' => [
                'tags' => ['Autenticação'],
                'summary' => 'Dados do usuário autenticado',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Usuário autenticado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'user' => ['$ref' => '#/components/schemas/User'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                ],
            ],
            'patch' => [
                'tags' => ['Autenticação'],
                'summary' => 'Atualiza os dados do usuário autenticado',
                'description' => 'Permite alterar apenas nome, email e senha do próprio usuário.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateSelfRequest'],
                            'example' => [
                                'name' => 'Meu Nome',
                                'email' => 'meu-email@example.com',
                                'password' => 'nova-senha',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Usuário atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/User'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/logout' => [
            'post' => [
                'tags' => ['Autenticação'],
                'summary' => 'Revoga o token atual',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Logout realizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'message' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                ],
            ],
        ],
        '/bazaar-items' => [
            'get' => [
                'tags' => ['Bazar'],
                'summary' => 'Lista itens do estoque do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de itens do bazar',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/BazaarItem'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Bazar'],
                'summary' => 'Adiciona item ao estoque do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreBazaarItemRequest'],
                            'example' => [
                                'suggested_price' => 49.9,
                                'name' => 'Camisa social',
                                'color' => 'Azul',
                                'size' => 'M',
                                'gender' => 'masculino',
                                'quantity' => 4,
                                'condition' => 'seminovo',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Item criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BazaarItem'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/bazaar-items/{bazaarItem}' => [
            'patch' => [
                'tags' => ['Bazar'],
                'summary' => 'Atualiza item do estoque do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'bazaarItem',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateBazaarItemRequest'],
                            'example' => [
                                'suggested_price' => 59.9,
                                'quantity' => 2,
                                'condition' => 'usado',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Item atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BazaarItem'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Bazar'],
                'summary' => 'Exclui item do estoque do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'bazaarItem',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Item excluído'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/bazaar-customers' => [
            'get' => [
                'tags' => ['Bazar'],
                'summary' => 'Lista clientes do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de clientes do bazar',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/BazaarCustomer'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Bazar'],
                'summary' => 'Cadastra cliente do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreBazaarCustomerRequest'],
                            'example' => [
                                'name' => 'Maria Silva',
                                'birth_date' => '1988-04-20',
                                'cpf' => '123.456.789-01',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Cliente criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BazaarCustomer'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/bazaar-customers/{bazaarCustomer}' => [
            'patch' => [
                'tags' => ['Bazar'],
                'summary' => 'Atualiza cliente do bazar',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'bazaarCustomer',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateBazaarCustomerRequest'],
                            'example' => [
                                'name' => 'Maria Oliveira',
                                'birth_date' => '1988-05-21',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Cliente atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BazaarCustomer'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/cashboxes' => [
            'get' => [
                'tags' => ['Caixas'],
                'summary' => 'Lista caixas',
                'description' => 'Requer token da diocese ou token de paróquia. Tokens de paróquia ficam restritos à própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de caixas',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Cashbox'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Caixas'],
                'summary' => 'Cria um caixa',
                'description' => 'Requer token da diocese ou token de paróquia. Tokens de paróquia ficam restritos à própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreCashboxRequest'],
                            'example' => [
                                'name' => 'Caixa Principal',
                                'balance' => 0,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Caixa criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Cashbox'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/cashboxes/{cashbox}' => [
            'patch' => [
                'tags' => ['Caixas'],
                'summary' => 'Atualiza um caixa ou registra movimentação',
                'description' => 'Quando amount é enviado, movement_type define entrada ou saída e uma movimentação é registrada no histórico.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'cashbox',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateCashboxRequest'],
                            'example' => [
                                'name' => 'Caixa Principal',
                                'family_id' => 1,
                                'amount' => 25.5,
                                'movement_type' => 'in',
                                'reason' => null,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Caixa atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Cashbox'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Caixas'],
                'summary' => 'Exclui um caixa',
                'description' => 'Requer token da diocese ou token de paróquia. Tokens de paróquia ficam restritos à própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'cashbox',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Caixa excluído'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/parish-inventories' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista inventarios paroquiais',
                'description' => 'Requer token da diocese ou token de paroquia. Tokens de paroquia ficam restritos a propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de inventarios paroquiais',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/ParishInventory'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Cria inventario paroquial',
                'description' => 'Requer token da diocese ou token de paroquia. Para token da diocese, parish_id e obrigatorio. Para token de paroquia, parish_id deve ser omitido.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreParishInventoryRequest'],
                            'example' => [
                                'parish_id' => 1,
                                'name' => 'Inventario Principal',
                                'description' => 'Itens da paroquia',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Inventario paroquial criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/ParishInventory'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/parish-inventories/{parishInventory}' => [
            'patch' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Atualiza inventario paroquial',
                'description' => 'Requer token da diocese ou token de paroquia. Tokens de paroquia so podem editar inventarios da propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parishInventory',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateParishInventoryRequest'],
                            'example' => [
                                'name' => 'Inventario Atualizado',
                                'description' => 'Descricao atualizada',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Inventario paroquial atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/ParishInventory'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Exclui inventario paroquial',
                'description' => 'Requer token da diocese ou token de paroquia. Tokens de paroquia so podem excluir inventarios da propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parishInventory',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Inventario paroquial excluido'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/valid-until-this-week' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista itens com validade nesta semana',
                'description' => 'Requer token da diocese ou token de paroquia. Tokens de paroquia ficam restritos a propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de Estoque paroquiais',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'valid_until_items_count' => ['type' => 'integer'],
                                        'valid_until_total_quantity' => ['type' => 'integer'],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/ParishInventoryItem'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/expired-items' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista itens expirados',
                'description' => 'Requer token da diocese ou token de paroquia. Tokens de paroquia ficam restritos a propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de Estoque paroquiais',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'expired_items_count' => ['type' => 'integer'],
                                        'expired_total_quantity' => ['type' => 'integer'],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/ParishInventoryItem'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/low-stock-items' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista itens em falta ou com quantidade baixa',
                'description' => 'Requer token da diocese ou token de paroquia. Tokens de paroquia ficam restritos a propria paroquia. Retorna itens com total_quantity menor ou igual ao threshold.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'threshold',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer', 'minimum' => 0, 'default' => 5],
                        'description' => 'Quantidade maxima para considerar estoque baixo. Zero representa item em falta.',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de itens em falta ou com estoque baixo',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'threshold' => ['type' => 'integer'],
                                        'missing_items_count' => ['type' => 'integer'],
                                        'low_stock_items_count' => ['type' => 'integer'],
                                        'low_stock_total_quantity' => ['type' => 'integer'],
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/ParishInventoryItem'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],

        '/parish-inventory-items' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista itens de inventario paroquial',
                'description' => 'Requer token da diocese ou token de paroquia. Aceita parish_inventory_id para filtrar por inventario.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parish_inventory_id',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer'],
                    ],
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'string'],
                        'description' => 'Busca por nome.',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de itens de inventario paroquial',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/ParishInventoryItem'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Cria item de inventario paroquial',
                'description' => 'Cria o item e registra a primeira quantidade com validade.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreParishInventoryItemRequest'],
                            'example' => [
                                'parish_inventory_id' => 1,
                                'name' => 'Arroz',
                                'description' => 'Pacote 5kg',
                                'quantity' => 12,
                                'valid_until' => '2026-12-31',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Item de inventario criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/ParishInventoryItem'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/parish-inventory-items/{parishId}' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista itens de inventario por paroquia',
                'description' => 'Requer token da diocese ou token de paroquia valido. Retorna os itens da paroquia informada na URL, sem forcar o escopo da paroquia do token.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parishId',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                    [
                        'name' => 'parish_inventory_id',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer'],
                    ],
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'string'],
                        'description' => 'Busca por nome.',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de itens da paroquia informada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/ParishInventoryItem'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/parish-inventory-items/{parishInventoryItem}' => [
            'patch' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Atualiza item de inventario paroquial',
                'description' => 'Atualiza nome e descricao do item. Quantidades sao gerenciadas no endpoint de quantidades.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parishInventoryItem',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateParishInventoryItemRequest'],
                            'example' => [
                                'name' => 'Arroz branco',
                                'description' => 'Pacote 5kg tipo 1',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Item de inventario atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/ParishInventoryItem'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Exclui item de inventario paroquial',
                'description' => 'Remove o item e suas quantidades.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parishInventoryItem',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Item de inventario excluido'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/parish-inventory-items/{parishInventoryItem}/quantities' => [
            'post' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Adiciona quantidade ao item de inventario',
                'description' => 'Registra uma nova quantidade com validade e soma ao total do item.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parishInventoryItem',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreParishInventoryItemQuantityRequest'],
                            'example' => [
                                'quantity' => 3,
                                'valid_until' => '2027-01-31',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Quantidade adicionada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/ParishInventoryItem'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/parish-inventory-repasses' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista repasses de itens para paroquias',
                'description' => 'Requer token da diocese ou token de paroquia. Tokens de paroquia retornam apenas repasses da propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parish_id',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer'],
                        'description' => 'Filtro por paroquia. Disponivel para token da diocese.',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de repasses',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/ParishInventoryRepasse'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Registra repasse de itens para uma paroquia',
                'description' => 'Requer token da diocese. Registra uma saida para prestacao de contas e adiciona os itens ao estoque da paroquia.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreParishInventoryRepasseRequest'],
                            'example' => [
                                'parish_id' => 1,
                                'delivered_at' => '2026-06-20 10:00:00',
                                'notes' => 'Repasse para prestacao de contas',
                                'items' => [
                                    [
                                        'name' => 'Arroz',
                                        'description' => 'Pacote 5kg',
                                        'quantity' => 20,
                                        'unit' => 'pacote',
                                        'valid_until' => '2026-12-31',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Repasse registrado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/ParishInventoryRepasse'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/parish-inventory-repasses/{parishInventoryRepasse}' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Detalha repasse de itens',
                'description' => 'Requer token da diocese ou token de paroquia dona do repasse.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parishInventoryRepasse',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Detalhe do repasse',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/ParishInventoryRepasse'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/basket-templates' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista cestas pre definidas',
                'description' => 'Requer token da diocese ou token de paroquia. Tokens de paroquia retornam apenas modelos da propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de cestas pre definidas',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/BasketTemplate'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Cria cesta pre definida',
                'description' => 'Define os itens e quantidades padrao de uma cesta. Para token da diocese, parish_id e obrigatorio.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreBasketTemplateRequest'],
                            'example' => [
                                'parish_id' => 1,
                                'name' => 'Cesta Basica',
                                'description' => 'Modelo mensal',
                                'items' => [
                                    [
                                        'parish_inventory_item_id' => 1,
                                        'quantity' => 2,
                                    ],
                                    [
                                        'parish_inventory_item_id' => 2,
                                        'quantity' => 1,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Cesta pre definida criada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BasketTemplate'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/basket-templates/{basketTemplate}' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Detalha cesta pre definida',
                'description' => 'Retorna os itens do template com os lotes disponiveis, quantidades e validades para montar a saida.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'basketTemplate',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Cesta pre definida',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BasketTemplate'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'patch' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Atualiza cesta pre definida',
                'description' => 'Atualiza os dados da cesta e, quando items for enviado, substitui a composicao da cesta.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'basketTemplate',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateBasketTemplateRequest'],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Cesta pre definida atualizada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BasketTemplate'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Exclui cesta pre definida',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'basketTemplate',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Cesta pre definida excluida'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/basket-deliveries' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Lista cestas entregues',
                'description' => 'Aceita family_id para filtrar entregas de uma familia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family_id',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de cestas entregues',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/BasketDelivery'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Registra entrega de cesta',
                'description' => 'family_id e obrigatorio. Com basket_template_id, items e opcional: se omitido, usa as quantidades padrao do template. Quando parish_inventory_item_quantity_id for omitido, a baixa usa automaticamente os lotes com validade mais proxima.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreBasketDeliveryRequest'],
                            'example' => [
                                'family_id' => 10,
                                'basket_template_id' => 3,
                                'delivered_at' => '2026-06-13 10:00:00',
                                'notes' => 'Entrega mensal',
                                'items' => [
                                    [
                                        'parish_inventory_item_id' => 1,
                                        'quantity' => 2,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Cesta entregue',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BasketDelivery'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/basket-deliveries/{basketDelivery}' => [
            'get' => [
                'tags' => ['Estoque Paroquiais'],
                'summary' => 'Detalha cesta entregue',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'basketDelivery',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Cesta entregue',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/BasketDelivery'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/families/{family}/basket-deliveries' => [
            'get' => [
                'tags' => ['FamÃ­lias'],
                'summary' => 'Lista cestas recebidas por uma familia',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de cestas recebidas pela familia',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/BasketDelivery'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/logs-cashboxes' => [
            'get' => [
                'tags' => ['Caixas'],
                'summary' => 'Lista histórico de movimentações dos caixas',
                'description' => 'Requer token da diocese ou token de paróquia. Tokens de paróquia retornam apenas movimentações dos caixas da própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de movimentações',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/LogsCashbox'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/logs-cashboxes/{logsCashbox}' => [
            'delete' => [
                'tags' => ['Caixas'],
                'summary' => 'Exclui uma movimentação do histórico',
                'description' => 'Requer token da diocese ou token de paróquia. Tokens de paróquia ficam restritos à própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'logsCashbox',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Movimentação excluída'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/parishes' => [
            'get' => [
                'tags' => ['Paróquias'],
                'summary' => 'Lista paróquias ativas',
                'responses' => [
                    '200' => [
                        'description' => 'Lista de paróquias',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Parish'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'post' => [
                'tags' => ['Paróquias'],
                'summary' => 'Cria uma paróquia',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreParishRequest'],
                            'example' => [
                                'name' => 'Paróquia São José',
                                'cnpj' => null,
                                'active' => true,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Paróquia criada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Parish'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/inactive-parishes' => [
            'get' => [
                'tags' => ['Paróquias'],
                'security' => [['bearerAuth' => []]],
                'summary' => 'Lista paróquias inativas',
                'responses' => [
                    '200' => [
                        'description' => 'Lista de paróquias inativas',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Parish'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/parishes/{parish}' => [
            'patch' => [
                'tags' => ['Paróquias'],
                'summary' => 'Atualiza uma paróquia',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parish',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateParishRequest'],
                            'example' => [
                                'name' => 'Paróquia São José',
                                'cnpj' => '12.345.678/0001-90',
                                'active' => true,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Paróquia atualizada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Parish'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Paróquias'],
                'summary' => 'Exclui uma paróquia',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parish',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Paróquia excluída'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/parishes/{parish}/activate' => [
            'patch' => [
                'tags' => ['Paróquias'],
                'summary' => 'Ativa uma paróquia',
                'description' => 'Requer token de admin da diocese.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'parish',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateParishRequest'],
                            'example' => [
                                'active' => true,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Paróquia ativada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Parish'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/families' => [
            'get' => [
                'tags' => ['Famílias'],
                'summary' => 'Lista famílias',
                'description' => 'Por padrão lista famílias das próprias paróquias do token. Admin da diocese pode informar all=true para listar todas as paróquias.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'string'],
                        'description' => 'Busca por nome da família/responsável, nome da mãe, endereço, observações ou paróquia.',
                    ],
                    [
                        'name' => 'all',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'boolean', 'default' => false],
                        'description' => 'Quando true, lista famílias de todas as paróquias. Permitido apenas para admin da diocese.',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de famílias',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Family'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Famílias'],
                'summary' => 'Cadastra família',
                'description' => 'Token da diocese deve informar parish_id. Token paroquial cadastra na própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreFamilyRequest'],
                            'example' => [
                                'parish_id' => 1,
                                'address' => 'Rua das Flores, 123',
                                'observations' => 'Recebe cesta básica mensal',
                                'responsible' => [
                                    'name' => 'Carla Silva',
                                    'cpf' => '222.333.444-55',
                                    'birth_date' => '1992-03-14',
                                    'mother_name' => 'Ana Silva',
                                    'relationship' => 'mae',
                                    'age' => 34,
                                    'registration_status' => 'ativo',
                                    'registration_date' => '2026-05-22',
                                    'personal_income' => 750.50,
                                ],
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Família criada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Family'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/inactive-families' => [
            'get' => [
                'tags' => ['Famílias'],
                'summary' => 'Lista famílias inativas',
                'description' => 'Por padrão lista famílias inativas das próprias paróquias do token. Admin da diocese pode informar all=true para listar todas as paróquias.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'search',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'string'],
                        'description' => 'Busca por nome da família/responsável, nome da mãe, endereço, observações ou paróquia.',
                    ],
                    [
                        'name' => 'all',
                        'in' => 'query',
                        'required' => false,
                        'schema' => ['type' => 'boolean', 'default' => false],
                        'description' => 'Quando true, lista famílias de todas as paróquias. Permitido apenas para admin da diocese.',
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de famílias',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/Family'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/families/{family}' => [
            'patch' => [
                'tags' => ['Famílias'],
                'summary' => 'Atualiza uma família',
                'description' => 'Admins paroquiais só podem editar famílias da própria paróquia. Apenas admin da diocese pode alterar parish_id.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateFamilyRequest'],
                            'example' => [
                                'address' => 'Rua Nova, 456',
                                'observations' => 'Cadastro atualizado',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Família atualizada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/Family'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Famílias'],
                'summary' => 'Exclui uma família',
                'description' => 'Admins paroquiais só podem excluir famílias da própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Família excluída'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/families/{family}/inactivate' => [
            'patch' => [
                'tags' => ['Famílias'],
                'summary' => 'Inativa uma família',
                'description' => 'Admins paroquiais só podem inativar famílias da própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Família inativada'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/families/{family}/activate' => [
            'patch' => [
                'tags' => ['Famílias'],
                'summary' => 'Ativa uma família',
                'description' => 'Admins paroquiais só podem ativar famílias da própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Família ativada'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/families/{family}/financial-records' => [
            'get' => [
                'tags' => ['Famílias'],
                'summary' => 'Lista registros financeiros da familia',
                'description' => 'Lista os registros financeiros vinculados a uma familia do escopo do token.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de registros financeiros da familia',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/LogsCashbox'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/home-visits' => [
            'get' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Lista visitas domiciliares recentes',
                'description' => 'Lista visitas domiciliares. Tokens paroquiais ficam restritos à própria paróquia e retornam visitas dos últimos 2 meses; token da diocese lista todas.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de visitas domiciliares',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/HomeVisit'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/home-visits/history' => [
            'get' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Lista histórico de visitas domiciliares',
                'description' => 'Lista todas as visitas do escopo do token em ordem decrescente de data.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Histórico de visitas domiciliares',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/HomeVisit'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/families/{family}/home-visits' => [
            'get' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Lista visitas domiciliares da família',
                'description' => 'Lista as visitas cadastradas para uma família do escopo do token.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de visitas da família',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/HomeVisit'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Agenda visita domiciliar',
                'description' => 'Agenda uma visita para a família informada na URL. O family_id vem do path.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreHomeVisitRequest'],
                            'example' => [
                                'user_id' => 1,
                                'visit_date' => '2026-06-15 14:00:00',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Visita domiciliar criada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/HomeVisit'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/home-visits/{homeVisit}' => [
            'patch' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Atualiza visita domiciliar',
                'description' => 'Atualiza dados gerais da visita domiciliar no escopo do token.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'homeVisit',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateHomeVisitRequest'],
                            'example' => [
                                'visit_date' => '2026-06-16 09:00:00',
                                'notes' => 'Família recebeu a equipe.',
                                'forwarding' => 'Encaminhar para acompanhamento social.',
                                'next_visit_date' => '2026-07-16 09:00:00',
                                'status' => 'completed',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Visita domiciliar atualizada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/HomeVisit'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Exclui visita domiciliar',
                'description' => 'Remove uma visita domiciliar do escopo do token.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'homeVisit',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Visita domiciliar excluída'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/home-visits/{homeVisit}/reschedule' => [
            'patch' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Reagenda visita domiciliar',
                'description' => 'Altera apenas a data da visita domiciliar.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'homeVisit',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/RescheduleHomeVisitRequest'],
                            'example' => [
                                'visit_date' => '2026-06-20 15:30:00',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Visita domiciliar reagendada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/HomeVisit'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/home-visits/{homeVisit}/cancel' => [
            'patch' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Cancela visita domiciliar',
                'description' => 'Marca a visita domiciliar como cancelada.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'homeVisit',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/CancelHomeVisitRequest'],
                            'example' => [
                                'status' => 'canceled',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Visita domiciliar cancelada',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/HomeVisit'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/home-visits/{homeVisit}/visit-record' => [
            'patch' => [
                'tags' => ['Visitas Domiciliares'],
                'summary' => 'Registra resultado da visita domiciliar',
                'description' => 'Salva anotações, encaminhamento, próxima visita e status após a realização da visita.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'homeVisit',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/VisitRecordHomeVisitRequest'],
                            'example' => [
                                'notes' => 'Família está com necessidade de cesta básica.',
                                'forwarding' => 'Inserir na próxima entrega mensal.',
                                'next_visit_date' => '2026-07-20 10:00:00',
                                'status' => 'completed',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Registro da visita salvo',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/HomeVisit'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/families/{family}/assisted-family-members' => [
            'get' => [
                'tags' => ['Famílias'],
                'summary' => 'Lista familiares assistidos',
                'description' => 'Lista os familiares assistidos cadastrados dentro de uma família.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de familiares assistidos',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/AssistedFamilyMember'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Famílias'],
                'summary' => 'Cadastra familiar assistido',
                'description' => 'Cria um familiar assistido dentro de uma família do escopo do token.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'family',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreAssistedFamilyMemberRequest'],
                            'example' => [
                                'name' => 'Julia Ferreira',
                                'cpf' => '111.222.333-44',
                                'birth_date' => '2014-05-20',
                                'mother_name' => 'Ana Ferreira',
                                'relationship' => 'filha',
                                'age' => 12,
                                'registration_status' => 'ativo',
                                'registration_date' => '2026-05-22',
                                'personal_income' => 750.50,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Familiar assistido criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/AssistedFamilyMember'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/assisted-family-members/search-by-cpf' => [
            'get' => [
                'tags' => ['Famílias'],
                'summary' => 'Busca familiar assistido por CPF',
                'description' => 'Retorna o familiar assistido pelo CPF informado. Aceita CPF formatado ou apenas dígitos.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'cpf',
                        'in' => 'query',
                        'required' => true,
                        'schema' => ['type' => 'string', 'maxLength' => 14],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Familiar assistido encontrado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/AssistedFamilyMember'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '404' => ['description' => 'Familiar assistido não encontrado'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/assisted-family-members/{assistedFamilyMember}' => [
            'patch' => [
                'tags' => ['Famílias'],
                'summary' => 'Atualiza familiar assistido',
                'description' => 'Atualiza os dados cadastrais e renda do familiar assistido.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'assistedFamilyMember',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateAssistedFamilyMemberRequest'],
                            'example' => [
                                'cpf' => '111.222.333-55',
                                'birth_date' => '2013-05-20',
                                'relationship' => 'filho',
                                'age' => 13,
                                'registration_status' => 'inativo',
                                'personal_income' => 900,
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Familiar assistido atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/AssistedFamilyMember'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/roles' => [
            'get' => [
                'tags' => ['Usuários'],
                'summary' => 'Lista perfis disponíveis',
                'description' => 'Retorna perfis do sistema e perfis de vínculo com paróquia para preencher selects no frontend.',
                'responses' => [
                    '200' => [
                        'description' => 'Lista de perfis',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'object',
                                            'properties' => [
                                                'system_roles' => [
                                                    'type' => 'array',
                                                    'items' => ['$ref' => '#/components/schemas/RoleOption'],
                                                ],
                                                'parish_roles' => [
                                                    'type' => 'array',
                                                    'items' => ['$ref' => '#/components/schemas/RoleOption'],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        '/users' => [
            'get' => [
                'tags' => ['Usuários'],
                'summary' => 'Lista usuários',
                'description' => 'Admin da diocese lista todos os usuários. Admin paroquial lista usuários vinculados à própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de usuários',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/User'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
            'post' => [
                'tags' => ['Usuários'],
                'summary' => 'Cria usuário administrativo',
                'description' => 'Token da diocese pode informar parish_ids. Token paroquial cria usuário na própria paróquia.',
                'security' => [['bearerAuth' => []]],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/StoreUserRequest'],
                            'example' => [
                                'name' => 'Novo Admin',
                                'email' => 'novo@example.com',
                                'password' => 'password',
                                'parish_ids' => [1],
                                'parish_role' => 'admin',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Usuário criado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/User'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
        ],
        '/inactive-users' => [
            'get' => [
                'tags' => ['UsuÃ¡rios'],
                'summary' => 'Lista usuarios inativos',
                'description' => 'Admin da diocese lista usuarios inativos. Admin paroquial lista usuarios inativos vinculados a propria paroquia.',
                'security' => [['bearerAuth' => []]],
                'responses' => [
                    '200' => [
                        'description' => 'Lista de usuarios inativos',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => [
                                            'type' => 'array',
                                            'items' => ['$ref' => '#/components/schemas/User'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/users/{user}' => [
            'patch' => [
                'tags' => ['Usuários'],
                'summary' => 'Atualiza um usuário',
                'description' => 'Admins podem editar usuários no seu escopo. O próprio usuário pode editar dados básicos também pelo endpoint /me.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'user',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => ['$ref' => '#/components/schemas/UpdateUserRequest'],
                            'example' => [
                                'name' => 'Usuário Atualizado',
                                'email' => 'usuario@example.com',
                                'parish_ids' => [1],
                                'parish_role' => 'admin',
                            ],
                        ],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Usuário atualizado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/User'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                    '422' => ['$ref' => '#/components/responses/ValidationError'],
                ],
            ],
            'delete' => [
                'tags' => ['Usuários'],
                'summary' => 'Exclui um usuário',
                'description' => 'Admins podem excluir usuários no seu escopo. Não é permitido excluir a própria conta por este endpoint.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'user',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Usuário excluído'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/users/{user}/inactivate' => [
            'patch' => [
                'tags' => ['UsuÃ¡rios'],
                'summary' => 'Inativa um usuario',
                'description' => 'Admins podem inativar usuarios no seu escopo. Nao e permitido inativar a propria conta por este endpoint. Os tokens do usuario inativado sao revogados.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'user',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '204' => ['description' => 'Usuario inativado'],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
        '/users/{user}/activate' => [
            'patch' => [
                'tags' => ['UsuÃ¡rios'],
                'summary' => 'Ativa um usuario',
                'description' => 'Admins podem ativar usuarios no seu escopo.',
                'security' => [['bearerAuth' => []]],
                'parameters' => [
                    [
                        'name' => 'user',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                    ],
                ],
                'responses' => [
                    '200' => [
                        'description' => 'Usuario ativado',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'data' => ['$ref' => '#/components/schemas/User'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    '401' => ['$ref' => '#/components/responses/Unauthenticated'],
                    '403' => ['$ref' => '#/components/responses/Forbidden'],
                ],
            ],
        ],
    ],
    'components' => [
        'securitySchemes' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'Sanctum token',
            ],
        ],
        'schemas' => [
            'LoginRequest' => [
                'type' => 'object',
                'required' => ['email', 'password'],
                'properties' => [
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password'],
                ],
            ],
            'ParishLoginRequest' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/LoginRequest'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'parish_id' => ['type' => 'integer', 'nullable' => true],
                            'parish_slug' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                ],
            ],
            'StoreBazaarItemRequest' => [
                'type' => 'object',
                'required' => ['suggested_price', 'name', 'quantity', 'condition'],
                'properties' => [
                    'suggested_price' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
                    'name' => ['type' => 'string'],
                    'color' => ['type' => 'string', 'nullable' => true],
                    'size' => ['type' => 'string', 'nullable' => true],
                    'gender' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer', 'minimum' => 0],
                    'condition' => ['type' => 'string'],
                ],
            ],
            'UpdateBazaarItemRequest' => [
                'type' => 'object',
                'properties' => [
                    'suggested_price' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
                    'name' => ['type' => 'string'],
                    'color' => ['type' => 'string', 'nullable' => true],
                    'size' => ['type' => 'string', 'nullable' => true],
                    'gender' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer', 'minimum' => 0],
                    'condition' => ['type' => 'string'],
                ],
            ],
            'StoreBazaarCustomerRequest' => [
                'type' => 'object',
                'required' => ['name', 'birth_date', 'cpf'],
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'birth_date' => ['type' => 'string', 'format' => 'date'],
                    'cpf' => ['type' => 'string', 'maxLength' => 14],
                ],
            ],
            'UpdateBazaarCustomerRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'birth_date' => ['type' => 'string', 'format' => 'date'],
                    'cpf' => ['type' => 'string', 'maxLength' => 14],
                ],
            ],
            'StoreCashboxRequest' => [
                'type' => 'object',
                'required' => ['name', 'balance'],
                'properties' => [
                    'parish_id' => ['type' => 'integer', 'description' => 'Obrigatório para token da diocese; opcional para token de paróquia, desde que seja a própria paróquia.'],
                    'name' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'balance' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
                ],
            ],
            'UpdateCashboxRequest' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'family_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'Família vinculada à movimentação. Deve pertencer à mesma paróquia do caixa.'],
                    'balance' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
                    'amount' => ['type' => 'number', 'format' => 'float', 'minimum' => 0.01],
                    'movement_type' => ['type' => 'string', 'enum' => ['in', 'out'], 'description' => 'Obrigatório quando amount for enviado.'],
                    'reason' => ['type' => 'string', 'nullable' => true, 'maxLength' => 100, 'description' => 'Obrigatório para movement_type igual a out.'],
                ],
            ],
            'StoreParishInventoryRequest' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'parish_id' => ['type' => 'integer', 'description' => 'Obrigatorio para token da diocese; omitido para token de paroquia.'],
                    'name' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'description' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                ],
            ],
            'UpdateParishInventoryRequest' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'description' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                ],
            ],
            'StoreParishInventoryItemRequest' => [
                'type' => 'object',
                'required' => ['parish_inventory_id', 'name', 'quantity', 'valid_until'],
                'properties' => [
                    'parish_inventory_id' => ['type' => 'integer'],
                    'name' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'description' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                    'quantity' => ['type' => 'integer', 'minimum' => 0],
                    'valid_until' => ['type' => 'string', 'format' => 'date'],
                ],
            ],
            'UpdateParishInventoryItemRequest' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'description' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                ],
            ],
            'StoreParishInventoryItemQuantityRequest' => [
                'type' => 'object',
                'required' => ['quantity', 'valid_until'],
                'properties' => [
                    'quantity' => ['type' => 'integer', 'minimum' => 0],
                    'valid_until' => ['type' => 'string', 'format' => 'date'],
                ],
            ],
            'StoreParishInventoryRepasseRequest' => [
                'type' => 'object',
                'required' => ['parish_id', 'items'],
                'properties' => [
                    'parish_id' => ['type' => 'integer'],
                    'delivered_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'notes' => ['type' => 'string', 'nullable' => true],
                    'items' => [
                        'type' => 'array',
                        'minItems' => 1,
                        'items' => ['$ref' => '#/components/schemas/StoreParishInventoryRepasseItemRequest'],
                    ],
                ],
            ],
            'StoreParishInventoryRepasseItemRequest' => [
                'type' => 'object',
                'required' => ['name', 'quantity', 'valid_until'],
                'properties' => [
                    'name' => ['type' => 'string', 'minLength' => 2, 'maxLength' => 255],
                    'description' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                    'quantity' => ['type' => 'integer', 'minimum' => 1],
                    'unit' => ['type' => 'string', 'nullable' => true, 'maxLength' => 50],
                    'valid_until' => ['type' => 'string', 'format' => 'date'],
                ],
            ],
            'StoreBasketTemplateRequest' => [
                'type' => 'object',
                'required' => ['name', 'items'],
                'properties' => [
                    'parish_id' => ['type' => 'integer', 'description' => 'Obrigatorio para token da diocese; opcional para token de paroquia, desde que seja a propria paroquia.'],
                    'name' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'description' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                    'active' => ['type' => 'boolean', 'default' => true],
                    'items' => [
                        'type' => 'array',
                        'minItems' => 1,
                        'items' => ['$ref' => '#/components/schemas/StoreBasketTemplateItemRequest'],
                    ],
                ],
            ],
            'UpdateBasketTemplateRequest' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => ['type' => 'string', 'minLength' => 3, 'maxLength' => 255],
                    'description' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                    'active' => ['type' => 'boolean'],
                    'items' => [
                        'type' => 'array',
                        'minItems' => 1,
                        'items' => ['$ref' => '#/components/schemas/StoreBasketTemplateItemRequest'],
                    ],
                ],
            ],
            'StoreBasketTemplateItemRequest' => [
                'type' => 'object',
                'required' => ['parish_inventory_item_id', 'quantity'],
                'properties' => [
                    'parish_inventory_item_id' => ['type' => 'integer'],
                    'quantity' => ['type' => 'integer', 'minimum' => 1],
                ],
            ],
            'StoreBasketDeliveryRequest' => [
                'type' => 'object',
                'required' => ['family_id'],
                'properties' => [
                    'family_id' => ['type' => 'integer', 'description' => 'Familia que recebe a cesta.'],
                    'basket_template_id' => ['type' => 'integer', 'nullable' => true, 'description' => 'Opcional. Quando informado e items for omitido, usa os itens padrao do template.'],
                    'delivered_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'notes' => ['type' => 'string', 'nullable' => true],
                    'items' => [
                        'type' => 'array',
                        'minItems' => 1,
                        'description' => 'Obrigatorio quando basket_template_id nao for enviado. Pode apontar para um lote especifico ou apenas para o item de inventario; nesse caso a validade mais proxima e usada automaticamente.',
                        'items' => ['$ref' => '#/components/schemas/StoreBasketDeliveryItemRequest'],
                    ],
                ],
            ],
            'StoreBasketDeliveryItemRequest' => [
                'type' => 'object',
                'required' => ['quantity'],
                'properties' => [
                    'parish_inventory_item_id' => ['type' => 'integer', 'description' => 'Item do inventario. Obrigatorio quando parish_inventory_item_quantity_id nao for enviado.'],
                    'parish_inventory_item_quantity_id' => ['type' => 'integer', 'description' => 'Lote escolhido, com sua validade especifica. Opcional.'],
                    'quantity' => ['type' => 'integer', 'minimum' => 1],
                ],
            ],
            'StoreParishRequest' => [
                'type' => 'object',
                'required' => ['name'],
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'cnpj' => ['type' => 'string', 'nullable' => true, 'maxLength' => 18],
                    'active' => ['type' => 'boolean', 'default' => true],
                ],
            ],
            'UpdateParishRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'cnpj' => ['type' => 'string', 'nullable' => true, 'maxLength' => 18],
                    'active' => ['type' => 'boolean'],
                ],
            ],
            'StoreFamilyRequest' => [
                'type' => 'object',
                'required' => ['responsible'],
                'properties' => [
                    'parish_id' => ['type' => 'integer', 'description' => 'Obrigatório para token da diocese.'],
                    'address' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                    'observations' => ['type' => 'string', 'nullable' => true],
                    'responsible' => [
                        'allOf' => [
                            ['$ref' => '#/components/schemas/StoreAssistedFamilyMemberRequest'],
                        ],
                        'description' => 'O nome da família é preenchido automaticamente com responsible.name.',
                    ],
                ],
            ],
            'UpdateFamilyRequest' => [
                'type' => 'object',
                'properties' => [
                    'parish_id' => ['type' => 'integer', 'description' => 'Permitido apenas para token da diocese.'],
                    'address' => ['type' => 'string', 'nullable' => true, 'maxLength' => 255],
                    'observations' => ['type' => 'string', 'nullable' => true],
                ],
            ],
            'StoreAssistedFamilyMemberRequest' => [
                'type' => 'object',
                'required' => ['name', 'mother_name', 'relationship', 'age', 'registration_status', 'registration_date', 'personal_income'],
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'cpf' => ['type' => 'string', 'nullable' => true, 'maxLength' => 14],
                    'birth_date' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                    'mother_name' => ['type' => 'string', 'maxLength' => 255],
                    'relationship' => ['type' => 'string', 'maxLength' => 50, 'example' => 'filho'],
                    'age' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 130],
                    'registration_status' => ['type' => 'string', 'maxLength' => 100],
                    'registration_date' => ['type' => 'string', 'format' => 'date'],
                    'personal_income' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
                ],
            ],
            'UpdateAssistedFamilyMemberRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'cpf' => ['type' => 'string', 'nullable' => true, 'maxLength' => 14],
                    'birth_date' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                    'mother_name' => ['type' => 'string', 'maxLength' => 255],
                    'relationship' => ['type' => 'string', 'maxLength' => 50, 'example' => 'filho'],
                    'age' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 130],
                    'registration_status' => ['type' => 'string', 'maxLength' => 100],
                    'registration_date' => ['type' => 'string', 'format' => 'date'],
                    'personal_income' => ['type' => 'number', 'format' => 'float', 'minimum' => 0],
                ],
            ],
            'StoreHomeVisitRequest' => [
                'type' => 'object',
                'required' => ['user_id', 'visit_date'],
                'properties' => [
                    'user_id' => ['type' => 'integer', 'description' => 'Usuário responsável pela visita.'],
                    'visit_date' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'UpdateHomeVisitRequest' => [
                'type' => 'object',
                'properties' => [
                    'visit_date' => ['type' => 'string', 'format' => 'date-time'],
                    'notes' => ['type' => 'string', 'nullable' => true, 'maxLength' => 1500],
                    'forwarding' => ['type' => 'string', 'nullable' => true, 'maxLength' => 500],
                    'next_visit_date' => ['type' => 'string', 'nullable' => true, 'format' => 'date-time'],
                    'status' => ['type' => 'string', 'enum' => ['pending', 'completed', 'canceled'], 'maxLength' => 50, 'example' => 'pending'],
                ],
            ],
            'RescheduleHomeVisitRequest' => [
                'type' => 'object',
                'required' => ['visit_date'],
                'properties' => [
                    'visit_date' => ['type' => 'string', 'format' => 'date-time'],
                ],
            ],
            'CancelHomeVisitRequest' => [
                'type' => 'object',
                'required' => ['status'],
                'properties' => [
                    'status' => ['type' => 'string', 'enum' => ['canceled'], 'maxLength' => 50, 'example' => 'canceled'],
                ],
            ],
            'VisitRecordHomeVisitRequest' => [
                'type' => 'object',
                'properties' => [
                    'notes' => ['type' => 'string', 'nullable' => true, 'maxLength' => 1500],
                    'forwarding' => ['type' => 'string', 'nullable' => true, 'maxLength' => 500],
                    'next_visit_date' => ['type' => 'string', 'nullable' => true, 'format' => 'date-time'],
                    'status' => ['type' => 'string', 'enum' => ['pending', 'completed', 'canceled'], 'maxLength' => 50, 'example' => 'completed'],
                ],
            ],
            'StoreUserRequest' => [
                'type' => 'object',
                'required' => ['name', 'email', 'password'],
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password', 'minLength' => 8],
                    'system_role' => ['type' => 'string', 'enum' => ['user', 'diocese_admin']],
                    'parish_ids' => ['type' => 'array', 'items' => ['type' => 'integer']],
                    'parish_role' => ['type' => 'string', 'enum' => ['member', 'admin', 'admin_no_visits'], 'default' => 'admin'],
                ],
            ],
            'UpdateSelfRequest' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password', 'minLength' => 8],
                ],
            ],
            'UpdateUserRequest' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/UpdateSelfRequest'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'system_role' => ['type' => 'string', 'enum' => ['user', 'diocese_admin']],
                            'parish_ids' => ['type' => 'array', 'items' => ['type' => 'integer']],
                            'parish_role' => ['type' => 'string', 'enum' => ['member', 'admin', 'admin_no_visits']],
                        ],
                    ],
                ],
            ],
            'Parish' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'slug' => ['type' => 'string'],
                    'cnpj' => ['type' => 'string', 'nullable' => true],
                    'active' => ['type' => 'boolean'],
                ],
            ],
            'BazaarItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'suggested_price' => ['type' => 'number', 'format' => 'float'],
                    'name' => ['type' => 'string'],
                    'color' => ['type' => 'string', 'nullable' => true],
                    'size' => ['type' => 'string', 'nullable' => true],
                    'gender' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer'],
                    'condition' => ['type' => 'string'],
                ],
            ],
            'BazaarCustomer' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'birth_date' => ['type' => 'string', 'format' => 'date'],
                    'cpf' => ['type' => 'string'],
                ],
            ],
            'Cashbox' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'balance' => ['type' => 'number', 'format' => 'float'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'ParishInventory' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'ParishInventoryItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'parish_inventory_id' => ['type' => 'integer'],
                    'total_quantity' => ['type' => 'integer'],
                    'valid_until_quantity' => ['type' => 'integer', 'description' => 'Soma das quantidades proximas da validade quando retornado por /valid-until-this-week.'],
                    'expired_quantity' => ['type' => 'integer', 'description' => 'Soma das quantidades vencidas quando retornado por /expired-items.'],
                    'stock_status' => ['type' => 'string', 'enum' => ['missing', 'low'], 'description' => 'Status retornado por /low-stock-items.'],
                    'quantities' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/ParishInventoryItemQuantity'],
                    ],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'ParishInventoryItemQuantity' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'quantity' => ['type' => 'integer'],
                    'valid_until' => ['type' => 'string', 'format' => 'date'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'ParishInventoryRepasse' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'parish_id' => ['type' => 'integer'],
                    'parish_name' => ['type' => 'string', 'nullable' => true],
                    'created_by' => ['type' => 'integer', 'nullable' => true],
                    'created_by_name' => ['type' => 'string', 'nullable' => true],
                    'movement_type' => ['type' => 'string', 'enum' => ['out']],
                    'delivered_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'notes' => ['type' => 'string', 'nullable' => true],
                    'items' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/ParishInventoryRepasseItem'],
                    ],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'ParishInventoryRepasseItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer'],
                    'unit' => ['type' => 'string', 'nullable' => true],
                    'valid_until' => ['type' => 'string', 'format' => 'date'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'BasketTemplate' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'parish_id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'description' => ['type' => 'string', 'nullable' => true],
                    'active' => ['type' => 'boolean'],
                    'items' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/BasketTemplateItem'],
                    ],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'BasketTemplateItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'parish_inventory_item_id' => ['type' => 'integer'],
                    'name' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer'],
                    'available_total_quantity' => ['type' => 'integer'],
                    'quantities' => [
                        'type' => 'array',
                        'description' => 'Lotes disponiveis para escolher a validade da saida.',
                        'items' => ['$ref' => '#/components/schemas/ParishInventoryItemQuantity'],
                    ],
                ],
            ],
            'BasketDelivery' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'parish_id' => ['type' => 'integer'],
                    'family_id' => ['type' => 'integer'],
                    'family_name' => ['type' => 'string', 'nullable' => true],
                    'basket_template_id' => ['type' => 'integer', 'nullable' => true],
                    'basket_template_name' => ['type' => 'string', 'nullable' => true],
                    'delivered_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'notes' => ['type' => 'string', 'nullable' => true],
                    'items' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/BasketDeliveryItem'],
                    ],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'updated_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                ],
            ],
            'BasketDeliveryItem' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'parish_inventory_item_id' => ['type' => 'integer'],
                    'parish_inventory_item_quantity_id' => ['type' => 'integer'],
                    'name' => ['type' => 'string', 'nullable' => true],
                    'quantity' => ['type' => 'integer'],
                    'valid_until' => ['type' => 'string', 'format' => 'date'],
                ],
            ],
            'LogsCashbox' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'cashbox_id' => ['type' => 'integer'],
                    'user_id' => ['type' => 'integer'],
                    'family_id' => ['type' => 'integer', 'nullable' => true],
                    'movement_type' => ['type' => 'string', 'enum' => ['in', 'out', 'update']],
                    'reason' => ['type' => 'string', 'nullable' => true],
                    'amount' => ['type' => 'number', 'format' => 'float'],
                    'created_at' => ['type' => 'string', 'format' => 'date-time', 'nullable' => true],
                    'cashbox' => [
                        'type' => 'object',
                        'nullable' => true,
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'parish_id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                        ],
                    ],
                    'user' => [
                        'type' => 'object',
                        'nullable' => true,
                        'properties' => [
                            'id' => ['type' => 'integer'],
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string', 'format' => 'email'],
                        ],
                    ],
                ],
            ],
            'HomeVisit' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'family_id' => ['type' => 'integer'],
                    'user_id' => ['type' => 'integer'],
                    'visit_date' => ['type' => 'string', 'format' => 'date-time'],
                    'notes' => ['type' => 'string', 'nullable' => true],
                    'forwarding' => ['type' => 'string', 'nullable' => true],
                    'next_visit_date' => ['type' => 'string', 'nullable' => true, 'format' => 'date-time'],
                    'status' => ['type' => 'string', 'enum' => ['pending', 'completed', 'canceled'], 'example' => 'pending'],
                ],
            ],
            'Family' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'parish_id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'address' => ['type' => 'string', 'nullable' => true],
                    'observations' => ['type' => 'string', 'nullable' => true],
                    'is_active' => ['type' => 'boolean'],
                    'parish' => ['$ref' => '#/components/schemas/Parish'],
                    'responsible' => ['$ref' => '#/components/schemas/AssistedFamilyMember'],
                    'assisted_family_members' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/AssistedFamilyMember'],
                    ],
                ],
            ],
            'AssistedFamilyMember' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'parish_id' => ['type' => 'integer'],
                    'family_id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'cpf' => ['type' => 'string', 'nullable' => true],
                    'birth_date' => ['type' => 'string', 'nullable' => true, 'format' => 'date'],
                    'mother_name' => ['type' => 'string'],
                    'relationship' => ['type' => 'string'],
                    'age' => ['type' => 'integer'],
                    'registration_status' => ['type' => 'string'],
                    'registration_date' => ['type' => 'string', 'format' => 'date'],
                    'personal_income' => ['type' => 'number', 'format' => 'float'],
                    'is_responsible' => ['type' => 'boolean'],
                ],
            ],
            'UserParish' => [
                'allOf' => [
                    ['$ref' => '#/components/schemas/Parish'],
                    [
                        'type' => 'object',
                        'properties' => [
                            'role' => ['type' => 'string', 'enum' => ['member', 'admin', 'admin_no_visits']],
                        ],
                    ],
                ],
            ],
            'User' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer'],
                    'name' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'system_role' => ['type' => 'string', 'enum' => ['user', 'diocese_admin']],
                    'active' => ['type' => 'boolean'],
                    'parishes' => [
                        'type' => 'array',
                        'items' => ['$ref' => '#/components/schemas/UserParish'],
                    ],
                ],
            ],
            'RoleOption' => [
                'type' => 'object',
                'properties' => [
                    'value' => ['type' => 'string'],
                    'label' => ['type' => 'string'],
                ],
            ],
            'ValidationError' => [
                'type' => 'object',
                'properties' => [
                    'message' => ['type' => 'string'],
                    'errors' => ['type' => 'object'],
                ],
            ],
        ],
        'responses' => [
            'LoginSuccess' => [
                'description' => 'Token criado com sucesso',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => [
                                'token_type' => ['type' => 'string', 'example' => 'Bearer'],
                                'access_token' => ['type' => 'string'],
                                'abilities' => ['type' => 'array', 'items' => ['type' => 'string']],
                                'user' => ['$ref' => '#/components/schemas/User'],
                                'parish' => ['$ref' => '#/components/schemas/Parish', 'nullable' => true],
                            ],
                        ],
                    ],
                ],
            ],
            'Unauthenticated' => [
                'description' => 'Não autenticado',
            ],
            'Forbidden' => [
                'description' => 'Sem permissão para executar esta ação',
            ],
            'ValidationError' => [
                'description' => 'Erro de validação',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ValidationError'],
                    ],
                ],
            ],
        ],
    ],
];
