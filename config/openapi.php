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
        ['name' => 'Paróquias'],
        ['name' => 'Famílias'],
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
                    'parish_id' => ['type' => 'integer', 'description' => 'Obrigatório para token da diocese; omitido para token de paróquia.'],
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
            'StoreUserRequest' => [
                'type' => 'object',
                'required' => ['name', 'email', 'password'],
                'properties' => [
                    'name' => ['type' => 'string', 'maxLength' => 255],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password', 'minLength' => 8],
                    'system_role' => ['type' => 'string', 'enum' => ['user', 'diocese_admin']],
                    'parish_ids' => ['type' => 'array', 'items' => ['type' => 'integer']],
                    'parish_role' => ['type' => 'string', 'enum' => ['member', 'admin'], 'default' => 'admin'],
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
                            'parish_role' => ['type' => 'string', 'enum' => ['member', 'admin']],
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
                            'role' => ['type' => 'string', 'enum' => ['member', 'admin']],
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
