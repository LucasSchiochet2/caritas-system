<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} Documentação da API</title>
        <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
        <style>
            body { margin: 0; background: #ffffff; }
        </style>
    </head>
    <body>
        <div id="swagger-ui"></div>
        <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
        <script>
            window.addEventListener('load', function () {
                window.ui = SwaggerUIBundle({
                    url: @json(route('docs.openapi.json', absolute: false)),
                    dom_id: '#swagger-ui',
                    deepLinking: true,
                    persistAuthorization: true,
                });

                localizeSwaggerUi();
            });

            function localizeSwaggerUi() {
                const translations = new Map(Object.entries({
                    'Authorize': 'Autorizar',
                    'Available authorizations': 'Autorizações disponíveis',
                    'Cancel': 'Cancelar',
                    'Clear': 'Limpar',
                    'Close': 'Fechar',
                    'Code': 'Código',
                    'Curl': 'cURL',
                    'Default': 'Padrão',
                    'Description': 'Descrição',
                    'Details': 'Detalhes',
                    'Download': 'Baixar',
                    'Execute': 'Executar',
                    'Explore': 'Explorar',
                    'Example Value': 'Valor de exemplo',
                    'Media type': 'Tipo de mídia',
                    'Model': 'Modelo',
                    'Name': 'Nome',
                    'No parameters': 'Sem parâmetros',
                    'Parameters': 'Parâmetros',
                    'Request body': 'Corpo da requisição',
                    'Request URL': 'URL da requisição',
                    'Response body': 'Corpo da resposta',
                    'Response headers': 'Cabeçalhos da resposta',
                    'Responses': 'Respostas',
                    'Schema': 'Esquema',
                    'Schemas': 'Esquemas',
                    'Select a definition': 'Selecione uma definição',
                    'Server response': 'Resposta do servidor',
                    'Servers': 'Servidores',
                    'Try it out': 'Testar',
                    'Value': 'Valor',
                }));

                const translateTextNodes = () => {
                    const root = document.querySelector('#swagger-ui');

                    if (! root) {
                        return;
                    }

                    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);

                    while (walker.nextNode()) {
                        const node = walker.currentNode;
                        const value = node.nodeValue.trim();

                        if (translations.has(value)) {
                            node.nodeValue = node.nodeValue.replace(value, translations.get(value));
                        }
                    }
                };

                const observer = new MutationObserver(translateTextNodes);
                observer.observe(document.querySelector('#swagger-ui'), {
                    childList: true,
                    subtree: true,
                    characterData: true,
                });

                translateTextNodes();
            }
        </script>
    </body>
</html>
