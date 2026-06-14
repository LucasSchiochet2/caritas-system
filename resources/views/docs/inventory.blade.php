<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Documentacao do Estoque</title>
</head>
<style>
    :root {
        --bg: #f6f8fb;
        --surface: #ffffff;
        --muted-surface: #eef3f7;
        --text: #1d2733;
        --muted: #64748b;
        --line: #d9e1ea;
        --accent: #0f766e;
        --accent-soft: #d9f3ef;
        --code-bg: #182230;
        --code-text: #e8eef7;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        background: var(--bg);
        color: var(--text);
        font-family: Arial, Helvetica, sans-serif;
        line-height: 1.55;
    }

    .layout {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        min-height: 100vh;
    }

    aside {
        position: sticky;
        top: 0;
        height: 100vh;
        overflow: auto;
        border-right: 1px solid var(--line);
        background: var(--surface);
        padding: 24px 20px;
    }

    main {
        padding: 32px;
    }

    .content {
        max-width: 1080px;
        margin: 0 auto;
    }

    .brand {
        display: block;
        margin-bottom: 4px;
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    h1 {
        margin: 0 0 12px;
        font-size: 34px;
        line-height: 1.15;
    }

    h2 {
        margin: 42px 0 14px;
        padding-top: 8px;
        border-top: 1px solid var(--line);
        font-size: 24px;
    }

    h3 {
        margin: 24px 0 10px;
        font-size: 18px;
    }

    p {
        margin: 0 0 14px;
    }

    ul,
    ol {
        margin: 0 0 18px 22px;
        padding: 0;
    }

    li {
        margin: 6px 0;
    }

    nav {
        margin-top: 14px;
    }

    nav a {
        display: block;
        border-radius: 6px;
        padding: 7px 8px;
        color: var(--text);
        font-size: 14px;
        text-decoration: none;
    }

    nav a:hover {
        background: var(--muted-surface);
    }

    .intro {
        margin-bottom: 24px;
        color: var(--muted);
        font-size: 17px;
    }

    .section {
        border: 1px solid var(--line);
        border-radius: 8px;
        background: var(--surface);
        padding: 22px;
        margin-bottom: 18px;
    }

    .endpoint {
        display: grid;
        grid-template-columns: 78px minmax(0, 1fr);
        gap: 10px;
        align-items: start;
        border-top: 1px solid var(--line);
        padding: 10px 0;
        font-family: Consolas, "Courier New", monospace;
        font-size: 14px;
    }

    .endpoint:first-of-type {
        border-top: 0;
    }

    .method {
        width: fit-content;
        min-width: 58px;
        border-radius: 5px;
        background: var(--accent-soft);
        color: var(--accent);
        padding: 2px 7px;
        text-align: center;
        font-weight: 700;
    }

    .note {
        border-left: 4px solid var(--accent);
        background: var(--accent-soft);
        padding: 12px 14px;
        margin: 16px 0;
    }

    code {
        border-radius: 4px;
        background: var(--muted-surface);
        padding: 2px 5px;
        font-family: Consolas, "Courier New", monospace;
        font-size: 0.95em;
    }

    pre {
        overflow: auto;
        border-radius: 8px;
        background: var(--code-bg);
        color: var(--code-text);
        padding: 16px;
        margin: 12px 0 18px;
        font-size: 14px;
        line-height: 1.45;
    }

    pre code {
        background: transparent;
        color: inherit;
        padding: 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 12px 0 18px;
    }

    th,
    td {
        border: 1px solid var(--line);
        padding: 10px;
        text-align: left;
        vertical-align: top;
    }

    th {
        background: var(--muted-surface);
    }

    @media (max-width: 900px) {
        .layout {
            grid-template-columns: 1fr;
        }

        aside {
            position: static;
            height: auto;
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }

        main {
            padding: 20px;
        }
    }
</style>

<body>
    <div class="layout">
        <aside>
            <span class="brand">{{ config('app.name') }}</span>
            <strong>Estoque e cestas</strong>
            <nav>
                <a href="#visao-geral">Visao geral</a>
                <a href="#permissoes">Permissoes</a>
                <a href="#inventarios">Inventarios</a>
                <a href="#itens-lotes">Itens e lotes</a>
                <a href="#validade">Validade</a>
                <a href="#regras">Regras de baixa</a>
                <a href="#templates">Cestas pre definidas</a>
                <a href="#saida">Saida de cestas</a>
                <a href="#familia">Cestas por familia</a>

                <a href="#erros">Erros comuns</a>
            </nav>
        </aside>

        <main>
            <div class="content">
                <span class="brand">API</span>
                <h1>Documentacao do estoque paroquial</h1>
                <p class="intro">Inventarios, itens com validade, cestas pre definidas e saidas por entrega a familias.
                </p>

                <section id="visao-geral" class="section">
                    <h2>Geral</h2>
                    <p>O estoque trabalha em tres camadas: inventario da paroquia, itens do inventario e lotes do item
                        com validade.</p>
                    <p>A saida acontece por cestas entregues a familias. A cesta pode vir de um template pre definido ou
                        ser montada na hora.</p>
                </section>

                <section id="permissoes" class="section">
                    <h2>Permissoes</h2>
                    <ul>
                        <li><strong>Token da diocese:</strong> pode operar em qualquer paroquia.</li>
                        <li><strong>Token paroquial:</strong> opera somente na propria paroquia do token.</li>
                    </ul>
                    <div>Quando um token paroquial tenta acessar dados de outra paroquia, a API responde
                        <code>403</code>.</div>
                </section>

                <section id="inventarios" class="section">
                    <h2>Inventarios</h2>
                    <p>Tabela principal: <code>parish_inventories</code></p>
                    <div class="endpoint"><span class="method">GET</span><span>/api/parish-inventories</span></div>

                </section>

                <section id="itens-lotes" class="section">
                    <h2>Itens e Lotes</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Tabela</th>
                                <th>Funcao</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>parish_inventory_items</code></td>
                                <td>Cadastro do item e total geral em <code>total_quantity</code>.</td>
                            </tr>
                            <tr>
                                <td><code>parish_inventory_item_quantities</code></td>
                                <td>Lotes do item, com <code>quantity</code> e <code>valid_until</code>.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="endpoint"><span class="method">GET</span><span>/api/parish-inventory-items</span></div>
                    <div class="endpoint"><span class="method">POST</span><span>/api/parish-inventory-items</span></div>
                    <div class="endpoint"><span
                            class="method">PATCH</span><span>/api/parish-inventory-items/{parishInventoryItem}</span>
                    </div>
                    <div class="endpoint"><span
                            class="method">DELETE</span><span>/api/parish-inventory-items/{parishInventoryItem}</span>
                    </div>
                    <div class="endpoint"><span
                            class="method">POST</span><span>/api/parish-inventory-items/{parishInventoryItem}/quantities</span>
                    </div>

                    <h3>Criar item com primeiro lote</h3>
                    <pre><code>{
  "parish_inventory_id": 1,
  "name": "Arroz",
  "description": "Pacote 5kg",
  "quantity": 12,
  "valid_until": "2026-12-31"
}</code></pre>

                    <h3>Adicionar novo lote</h3>
                    <pre><code>{
  "quantity": 3,
  "valid_until": "2027-01-31"
}</code></pre>
                </section>

                <section id="validade" class="section">
                    <h2>Validade</h2>
                    <div class="endpoint"><span class="method">GET</span><span>/api/valid-until-this-week</span></div>
                    <p>Retorna lotes vencendo entre hoje e os proximos 7 dias.</p>
                    <div class="endpoint"><span class="method">GET</span><span>/api/expired-items</span></div>
                    <p>Retorna lotes vencidos.</p>

                    <pre><code>{
  "valid_until_items_count": 2,
  "valid_until_total_quantity": 10,
  "data": [
    {
      "id": 1,
      "name": "Arroz",
      "valid_until_quantity": 7,
      "quantities": [
        {
          "id": 2,
          "quantity": 7,
          "valid_until": "2026-06-15"
        }
      ]
    }
  ]
}</code></pre>
                </section>
                <section id="regras" class="section">
                    <h2>Regras de Baixa</h2>
                    <ol>
                        <li>Valida se a familia pertence a paroquia permitida.</li>
                        <li>Valida se o item ou lote pertence a mesma paroquia da familia.</li>
                        <li>Se o lote foi escolhido, baixa daquele lote.</li>
                        <li>Se o lote nao foi escolhido, baixa dos lotes com menor <code>valid_until</code> primeiro.
                        </li>
                        <li>Cria os registros em <code>basket_delivery_items</code>.</li>
                        <li>Decrementa <code>parish_inventory_item_quantities.quantity</code>.</li>
                        <li>Decrementa <code>parish_inventory_items.total_quantity</code>.</li>
                    </ol>
                    <div class="note">A entrega roda dentro de uma transacao. Se qualquer item nao tiver saldo
                        suficiente, nada e baixado.</div>
                </section>
                <section id="templates" class="section">
                    <h2>Cestas Pre Definidas</h2>
                    <p>Tabelas: <code>basket_templates</code> e <code>basket_template_items</code></p>
                    <div class="endpoint"><span class="method">GET</span><span>/api/basket-templates</span></div>
                    <div class="endpoint"><span class="method">POST</span><span>/api/basket-templates</span></div>
                    <div class="endpoint"><span
                            class="method">GET</span><span>/api/basket-templates/{basketTemplate}</span></div>
                    <div class="endpoint"><span
                            class="method">PATCH</span><span>/api/basket-templates/{basketTemplate}</span></div>
                    <div class="endpoint"><span
                            class="method">DELETE</span><span>/api/basket-templates/{basketTemplate}</span></div>

                    <h3>Criar template</h3>
                    <pre><code>{
  "parish_id": 1,
  "name": "Cesta Basica",
  "description": "Modelo mensal",
  "items": [
    {
      "parish_inventory_item_id": 1,
      "quantity": 2
    },
    {
      "parish_inventory_item_id": 2,
      "quantity": 1
    }
  ]
}</code></pre>

                    <h3>GET do template para montar saida</h3>
                    <pre><code>{
  "data": {
    "id": 3,
    "name": "Cesta Basica",
    "items": [
      {
        "parish_inventory_item_id": 1,
        "name": "Arroz",
        "quantity": 2,
        "available_total_quantity": 10,
        "quantities": [
          {
            "id": 15,
            "quantity": 4,
            "valid_until": "2026-07-01"
          },
          {
            "id": 16,
            "quantity": 6,
            "valid_until": "2026-08-01"
          }
        ]
      }
    ]
  }
}</code></pre>
                </section>

                <section id="saida" class="section">
                    <h2>Saida de Cestas</h2>
                    <p>A saida sempre exige <code>family_id</code>.</p>
                    <p>Tabelas: <code>basket_deliveries</code> e <code>basket_delivery_items</code></p>
                    <div class="endpoint"><span class="method">GET</span><span>/api/basket-deliveries</span></div>
                    <div class="endpoint"><span class="method">POST</span><span>/api/basket-deliveries</span></div>
                    <div class="endpoint"><span
                            class="method">GET</span><span>/api/basket-deliveries/{basketDelivery}</span></div>

                    <h3>Template com baixa automatica</h3>
                    <pre><code>{
  "family_id": 10,
  "basket_template_id": 3,
  "notes": "Entrega mensal"
}</code></pre>

                    <h3>Template com quantidade editada</h3>
                    <pre><code>{
  "family_id": 10,
  "basket_template_id": 3,
  "items": [
    {
      "parish_inventory_item_id": 1,
      "quantity": 3
    }
  ]
}</code></pre>

                    <h3>Escolhendo a validade</h3>
                    <pre><code>{
  "family_id": 10,
  "basket_template_id": 3,
  "items": [
    {
      "parish_inventory_item_quantity_id": 15,
      "quantity": 2
    }
  ]
}</code></pre>

                    <h3>Cesta criada na hora</h3>
                    <pre><code>{
  "family_id": 10,
  "notes": "Cesta montada na hora",
  "items": [
    {
      "parish_inventory_item_id": 1,
      "quantity": 2
    },
    {
      "parish_inventory_item_quantity_id": 22,
      "quantity": 1
    }
  ]
}</code></pre>
                </section>

                <section id="familia" class="section">
                    <h2>Cestas Recebidas por Familia</h2>
                    <div class="endpoint"><span
                            class="method">GET</span><span>/api/families/{family}/basket-deliveries</span></div>
                    <pre><code>{
  "data": [
    {
      "id": 1,
      "family_id": 10,
      "family_name": "Familia Recebedora",
      "basket_template_id": 3,
      "basket_template_name": "Cesta Basica",
      "items": [
        {
          "parish_inventory_item_id": 1,
          "parish_inventory_item_quantity_id": 15,
          "name": "Arroz",
          "quantity": 2,
          "valid_until": "2026-07-01"
        }
      ]
    }
  ]
}</code></pre>
                </section>



                <section id="erros" class="section">
                    <h2>Erros Comuns</h2>
                    <h3>403</h3>
                    <p>Token paroquial tentando acessar ou movimentar dados de outra paroquia.</p>
                    <h3>422</h3>
                    <ul>
                        <li><code>family_id</code> ausente na entrega.</li>
                        <li>Entrega sem <code>basket_template_id</code> e sem <code>items</code>.</li>
                        <li>Item sem <code>parish_inventory_item_id</code> e sem
                            <code>parish_inventory_item_quantity_id</code>.</li>
                        <li>Quantidade maior que o saldo disponivel.</li>
                        <li>Lote escolhido nao pertence ao item informado.</li>
                    </ul>
                </section>
            </div>
        </main>
    </div>
</body>

</html>
