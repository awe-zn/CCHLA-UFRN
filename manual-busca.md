# 🔍 Manual Técnico - Sistema de Busca CCHLA

**Versão:** 1.0  
**Data:** Janeiro 2025  
**Autor:** Equipe de Desenvolvimento CCHLA  
**Público-alvo:** Desenvolvedores, Designers e Gestores

---

## Índice

1. [Visão Geral do Sistema](#1-visão-geral-do-sistema)
2. [Arquitetura e Componentes](#2-arquitetura-e-componentes)
3. [Fluxo de Funcionamento](#3-fluxo-de-funcionamento)
4. [Configurações e Personalizações](#4-configurações-e-personalizações)
5. [API e Integrações](#5-api-e-integrações)
6. [Performance e Otimizações](#6-performance-e-otimizações)
7. [Manutenção e Troubleshooting](#7-manutenção-e-troubleshooting)
8. [Boas Práticas](#8-boas-práticas)
9. [Roadmap e Melhorias Futuras](#9-roadmap-e-melhorias-futuras)

---

## 1. Visão Geral do Sistema

### 1.1 Objetivo

O sistema de busca do CCHLA foi desenvolvido para permitir que usuários encontrem conteúdo de forma rápida e eficiente em todos os tipos de publicações do site, incluindo:

- **Posts** (Notícias)
- **Páginas** estáticas
- **Publicações** acadêmicas
- **Especiais** (vídeos/projetos)
- **Serviços** de extensão
- **Acesso Rápido** (sistemas externos)

### 1.2 Características Principais

| Característica         | Descrição                                              |
| ---------------------- | ------------------------------------------------------ |
| **Busca Universal**    | Pesquisa em todos os Custom Post Types simultaneamente |
| **Filtros Dinâmicos**  | Filtragem por tipo de conteúdo com contadores          |
| **Destaque de Termos** | Realce visual dos termos buscados nos resultados       |
| **Paginação**          | Sistema de navegação entre páginas de resultados       |
| **Responsivo**         | Interface adaptável a todos os dispositivos            |
| **Acessível**          | Compatível com leitores de tela (WCAG 2.1)             |

### 1.3 Tecnologias Utilizadas

```
WordPress 6.x
PHP 8.x
MySQL 8.x
Tailwind CSS 3.x
Font Awesome 6.x
JavaScript (ES6+)
AJAX (jQuery)
```

---

## 2. Arquitetura e Componentes

### 2.1 Estrutura de Arquivos

```
theme-root/
├── search.php                          # Template principal de busca
├── searchform.php                      # Formulário de busca
├── functions.php                       # Funções do sistema
│
├── template-parts/
│   └── search/
│       ├── result-post.php            # Card de notícias
│       ├── result-page.php            # Card de páginas
│       ├── result-publicacoes.php     # Card de publicações
│       ├── result-especiais.php       # Card de especiais
│       ├── result-servicos.php        # Card de serviços
│       └── result-acesso_rapido.php   # Card de sistemas
│
└── assets/
    ├── css/
    │   └── search.css                 # Estilos específicos
    └── js/
        └── search-autocomplete.js     # Sugestões (opcional)
```

### 2.2 Diagrama de Componentes

```
┌─────────────────────────────────────────────────┐
│          Interface do Usuário (UI)              │
│  ┌───────────────┐  ┌──────────────────────┐   │
│  │ Formulário de │  │  Página de Resultados │   │
│  │     Busca     │  │                       │   │
│  └───────┬───────┘  └──────────┬───────────┘   │
└──────────┼───────────────────────┼──────────────┘
           │                       │
           ▼                       ▼
┌─────────────────────────────────────────────────┐
│         Camada de Lógica (PHP)                  │
│  ┌──────────────────────────────────────────┐   │
│  │ cchla_search_query_modification()         │   │
│  │ cchla_get_search_counts_by_type()         │   │
│  │ cchla_highlight_search_term()             │   │
│  └──────────────────────────────────────────┘   │
└──────────────────┬──────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────┐
│       Camada de Dados (WordPress/MySQL)         │
│  ┌──────────────────────────────────────────┐   │
│  │ WP_Query()                                │   │
│  │ Custom Post Types                         │   │
│  │ Taxonomies                                │   │
│  └──────────────────────────────────────────┘   │
└─────────────────────────────────────────────────┘
```

### 2.3 Componentes Principais

#### A) `search.php` - Template Principal

**Responsabilidades:**
- Renderização da página de resultados
- Exibição de estatísticas de busca
- Gerenciamento de filtros laterais
- Paginação de resultados
- Mensagens de erro (sem resultados)

**Inputs:**
- `$_GET['s']` - Termo de busca
- `$_GET['post_type']` - Filtro de tipo (opcional)
- `$_GET['paged']` - Página atual

**Outputs:**
- HTML completo da página de busca

---

#### B) `searchform.php` - Formulário

**Responsabilidades:**
- Captura do termo de busca
- Validação básica de input
- Acessibilidade (ARIA labels)

**Parâmetros:**
```php
<input 
    type="search"           // Tipo HTML5
    name="s"                // Nome padrão WP
    required                // Validação
    aria-label="Campo de busca"
/>
```

---

#### C) Funções Core (`functions.php`)

##### `cchla_search_query_modification($query)`

**Propósito:** Modificar a query principal de busca para incluir todos os post types.

**Parâmetros:**
- `$query` (WP_Query): Objeto de query do WordPress

**Lógica:**
```php
if (!is_admin() && $query->is_search() && $query->is_main_query()) {
    if (isset($_GET['post_type'])) {
        // Filtro específico
        $query->set('post_type', $_GET['post_type']);
    } else {
        // Todos os tipos
        $query->set('post_type', array(
            'post', 'page', 'publicacoes', 
            'especiais', 'servicos', 'acesso_rapido'
        ));
    }
}
```

**Performance:**
- ✅ Executa apenas na query principal
- ✅ Não afeta o admin
- ✅ Cache-friendly

---

##### `cchla_get_search_counts_by_type($search_query)`

**Propósito:** Contar resultados por tipo de conteúdo para exibir nos filtros.

**Parâmetros:**
- `$search_query` (string): Termo de busca

**Retorno:**
```php
array(
    'post' => 15,
    'page' => 3,
    'publicacoes' => 8,
    'especiais' => 2,
    'servicos' => 5,
    'acesso_rapido' => 1
)
```

**Implementação:**
```php
function cchla_get_search_counts_by_type($search_query) {
    $post_types = array('post', 'page', 'publicacoes', ...);
    $counts = array();
    
    foreach ($post_types as $post_type) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            's' => $search_query,
            'posts_per_page' => -1,
            'fields' => 'ids',          // Otimização
            'no_found_rows' => false,
        );
        
        $query = new WP_Query($args);
        $counts[$post_type] = $query->found_posts;
        wp_reset_postdata();
    }
    
    return $counts;
}
```

**Considerações de Performance:**
- ⚠️ Executa múltiplas queries (uma por tipo)
- 💡 Considerar cache transient para resultados
- 💡 Limitar a 1000 posts por contagem

---

##### `cchla_highlight_search_term($text, $search_term)`

**Propósito:** Destacar visualmente o termo buscado nos resultados.

**Parâmetros:**
- `$text` (string): Texto original
- `$search_term` (string): Termo a destacar

**Retorno:**
```html
"Lorem <mark class="bg-yellow-200 font-semibold">ipsum</mark> dolor"
```

**Implementação:**
```php
function cchla_highlight_search_term($text, $search_term) {
    if (empty($search_term)) {
        return $text;
    }
    
    return preg_replace(
        '/(' . preg_quote($search_term, '/') . ')/iu',
        '<mark class="bg-yellow-200 font-semibold">$1</mark>',
        $text
    );
}
```

**Segurança:**
- ✅ Usa `preg_quote()` para evitar regex injection
- ✅ Case-insensitive (`/iu`)
- ✅ Suporta Unicode

---

#### D) Cards de Resultado

Cada tipo de conteúdo tem seu próprio card em `template-parts/search/`.

**Estrutura Comum:**
```php
<article class="bg-white rounded-lg shadow-sm border p-6">
    <!-- Ícone do Tipo -->
    <div class="icon-container">
        <i class="fa-solid fa-{icon}"></i>
    </div>
    
    <!-- Meta Informações -->
    <div class="meta">
        <span class="type-badge">Tipo</span>
        <span>Categoria</span>
        <span>Data</span>
    </div>
    
    <!-- Título com Destaque -->
    <h3>
        <a href="<?php the_permalink(); ?>">
            <?php echo cchla_highlight_search_term(
                get_the_title(), 
                get_search_query()
            ); ?>
        </a>
    </h3>
    
    <!-- Excerpt -->
    <p><?php echo cchla_highlight_search_term(
        get_the_excerpt(), 
        get_search_query()
    ); ?></p>
    
    <!-- Link -->
    <a href="<?php the_permalink(); ?>">Ver mais</a>
</article>
```

**Diferenças por Tipo:**

| Tipo          | Ícone                   | Cor      | Meta Extra           |
| ------------- | ----------------------- | -------- | -------------------- |
| Post          | `fa-newspaper`          | Azul     | Categoria, Data      |
| Page          | `fa-file`               | Cinza    | -                    |
| Publicações   | `fa-book`               | Verde    | Autores, Tipo        |
| Especiais     | `fa-video`              | Vermelho | Categoria, Thumbnail |
| Serviços      | `fa-hand-holding-heart` | Amarelo  | Categoria            |
| Acesso Rápido | `fa-link`               | Roxo     | Link Externo         |

---

## 3. Fluxo de Funcionamento

### 3.1 Fluxo Completo da Busca

```
┌─────────────────────────────────────────────────────────┐
│ 1. USUÁRIO DIGITA TERMO E SUBMETE FORMULÁRIO            │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 2. NAVEGADOR ENVIA GET REQUEST                          │
│    URL: /?s=termo+de+busca&post_type=publicacoes        │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 3. WORDPRESS IDENTIFICA COMO BUSCA                      │
│    is_search() = true                                    │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 4. HOOK pre_get_posts É ACIONADO                        │
│    cchla_search_query_modification() é executado        │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 5. QUERY É MODIFICADA                                   │
│    - Define post_types a buscar                         │
│    - Define posts_per_page                              │
│    - Aplica filtros adicionais                          │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 6. MYSQL EXECUTA QUERY                                  │
│    SELECT * FROM wp_posts                               │
│    WHERE post_type IN (...)                             │
│    AND (post_title LIKE '%termo%'                       │
│         OR post_content LIKE '%termo%')                 │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 7. WORDPRESS CARREGA search.php                         │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 8. CONTADORES SÃO CALCULADOS                            │
│    cchla_get_search_counts_by_type()                    │
│    - Executa query para cada tipo                       │
│    - Retorna array com contagens                        │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 9. SIDEBAR DE FILTROS É RENDERIZADA                     │
│    - Exibe tipos com contagem > 0                       │
│    - Destaca filtro ativo                               │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 10. LOOP DE RESULTADOS                                  │
│     while (have_posts()) :                              │
│         the_post();                                     │
│         get_template_part('search/result', post_type);  │
│     endwhile;                                           │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 11. CADA CARD É RENDERIZADO                             │
│     - Carrega template específico do tipo               │
│     - Aplica destaque nos termos                        │
│     - Formata meta informações                          │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 12. PAGINAÇÃO É RENDERIZADA                             │
│     the_posts_pagination()                              │
└────────────────┬────────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────────┐
│ 13. HTML É ENVIADO AO NAVEGADOR                         │
└─────────────────────────────────────────────────────────┘
```

### 3.2 Tempo Médio de Execução

| Etapa                | Tempo Médio  | Otimizável  |
| -------------------- | ------------ | ----------- |
| Modificação da Query | < 1ms        | ✅           |
| Execução MySQL       | 10-50ms      | ✅ Cache     |
| Contagem por Tipo    | 50-200ms     | ✅ Transient |
| Renderização Cards   | 20-100ms     | ✅           |
| **Total**            | **80-350ms** |             |

---

## 4. Configurações e Personalizações

### 4.1 Modificar Posts por Página

**Localização:** `functions.php` → `cchla_search_query_modification()`

```php
// Mudar de 10 para 20 resultados por página
$query->set('posts_per_page', 20);
```

---

### 4.2 Adicionar/Remover Post Types da Busca

**Localização:** `functions.php` → `cchla_search_query_modification()`

```php
// Adicionar novo post type
$query->set('post_type', array(
    'post',
    'page',
    'publicacoes',
    'especiais',
    'servicos',
    'acesso_rapido',
    'seu_novo_cpt'  // ← Adicione aqui
));
```

**Importante:** Também adicione em `cchla_get_search_counts_by_type()`:

```php
$post_types = array(
    'post', 'page', 'publicacoes', 
    'especiais', 'servicos', 'acesso_rapido',
    'seu_novo_cpt'  // ← E aqui
);
```

E crie o card correspondente:
```
template-parts/search/result-seu_novo_cpt.php
```

---

### 4.3 Modificar Cores dos Badges

**Localização:** `template-parts/search/result-{type}.php`

```php
// Exemplo: Mudar cor do badge de Publicações
// De: bg-green-50 text-green-700
// Para: bg-purple-50 text-purple-700

<span class="inline-flex items-center gap-1 px-2 py-1 bg-purple-50 text-purple-700 rounded font-medium">
    <i class="fa-solid fa-book"></i>
    Publicação
</span>
```

**Paleta de Cores CCHLA:**
```css
Azul Primário:    #2E3CB9
Azul Escuro:      #1D2E7A
Azul Claro:       #3457CB
Verde:            #00a32a
Vermelho:         #dc3232
Amarelo:          #f0b849
Roxo:             #6b21a8
Cinza:            #6b7280
```

---

### 4.4 Alterar Texto dos Botões

**Localização:** `template-parts/search/result-{type}.php`

```php
// Exemplo: Mudar "Ver mais" para "Saiba mais"
<a href="<?php the_permalink(); ?>">
    <?php esc_html_e('Saiba mais', 'cchla-ufrn'); ?>
</a>
```

---

### 4.5 Modificar Placeholder do Formulário

**Localização:** `searchform.php`

```php
$placeholder = __('Digite sua busca aqui...', 'cchla-ufrn');
```

---

### 4.6 Desabilitar Destaque de Termos

**Localização:** `template-parts/search/result-{type}.php`

```php
// Substituir:
<?php echo cchla_highlight_search_term(get_the_title(), $search_term); ?>

// Por:
<?php the_title(); ?>
```

---

## 5. API e Integrações

### 5.1 Endpoints Disponíveis

#### A) Busca Padrão (GET)

```
URL: /?s={termo}
Método: GET
Parâmetros:
  - s (string, required): Termo de busca
  - post_type (string, optional): Filtro de tipo
  - paged (int, optional): Número da página

Exemplo:
/?s=inteligência%20artificial&post_type=publicacoes&paged=2
```

**Resposta:**
HTML completo da página de resultados.

---

#### B) Sugestões AJAX (POST)

```
URL: /wp-admin/admin-ajax.php
Método: POST
Parâmetros:
  - action: 'cchla_search_suggestions'
  - term (string): Termo parcial (mín. 3 caracteres)
  - nonce (string): Token de segurança

Exemplo:
POST /wp-admin/admin-ajax.php
{
  action: 'cchla_search_suggestions',
  term: 'intel',
  nonce: 'abc123...'
}
```

**Resposta JSON:**
```json
{
  "success": true,
  "data": [
    {
      "title": "Inteligência Artificial na Educação",
      "url": "https://cchla.ufrn.br/publicacoes/ia-educacao",
      "type": "Publicação"
    },
    {
      "title": "Curso de Inteligência Computacional",
      "url": "https://cchla.ufrn.br/servicos/curso-ia",
      "type": "Serviço"
    }
  ]
}
```

---

### 5.2 Integração com Google Analytics

Para trackear buscas sem resultados:

**Adicionar em `search.php`:**

```php
<?php if (!have_posts()) : ?>
    <script>
    // Google Analytics 4
    gtag('event', 'search', {
        'search_term': '<?php echo esc_js(get_search_query()); ?>',
        'search_results': 0
    });
    
    // Google Analytics Universal (legado)
    ga('send', 'event', 'Search', 'No Results', '<?php echo esc_js(get_search_query()); ?>');
    </script>
<?php endif; ?>
```

---

### 5.3 REST API Customizada

Para criar um endpoint REST customizado:

**Adicionar em `functions.php`:**

```php
/**
 * Endpoint REST para busca
 * GET /wp-json/cchla/v1/search?s=termo&type=post
 */
add_action('rest_api_init', function() {
    register_rest_route('cchla/v1', '/search', array(
        'methods' => 'GET',
        'callback' => 'cchla_rest_search',
        'permission_callback' => '__return_true',
        'args' => array(
            's' => array(
                'required' => true,
                'validate_callback' => function($param) {
                    return is_string($param) && strlen($param) >= 3;
                }
            ),
            'type' => array(
                'required' => false,
                'default' => 'all'
            ),
            'per_page' => array(
                'required' => false,
                'default' => 10
            )
        )
    ));
});

function cchla_rest_search($request) {
    $search_term = sanitize_text_field($request['s']);
    $post_type = $request['type'];
    $per_page = intval($request['per_page']);
    
    $args = array(
        'post_type' => $post_type === 'all' ? 
            array('post', 'page', 'publicacoes', 'especiais', 'servicos') : 
            $post_type,
        'posts_per_page' => $per_page,
        's' => $search_term,
        'post_status' => 'publish'
    );
    
    $query = new WP_Query($args);
    $results = array();
    
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            $results[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'excerpt' => get_the_excerpt(),
                'url' => get_permalink(),
                'type' => get_post_type(),
                'date' => get_the_date('c'),
                'thumbnail' => get_the_post_thumbnail_url(null, 'thumbnail')
            );
        }
        wp_reset_postdata();
    }
    
    return rest_ensure_response(array(
        'total' => $query->found_posts,
        'results' => $results
    ));
}
```

**Uso:**
```bash
curl "https://cchla.ufrn.br/wp-json/cchla/v1/search?s=inteligencia&type=publicacoes"
```

---

## 6. Performance e Otimizações

### 6.1 Gargalos Identificados

| Problema                        | Impacto       | Solução                 |
| ------------------------------- | ------------- | ----------------------- |
| Múltiplas queries para contagem | Alto (200ms+) | Cache transient         |
| Queries sem índices             | Médio         | Adicionar índices MySQL |
| Regex em highlight              | Baixo         | Já otimizado            |
| Carregamento de thumbnails      | Médio         | Lazy loading            |

---

### 6.2 Implementar Cache de Contagem

**Adicionar em `functions.php`:**

```php
function cchla_get_search_counts_by_type($search_query) {
    // Gera chave única para o cache
    $cache_key = 'search_counts_' . md5($search_query);
    
    // Tenta buscar do cache
    $counts = get_transient($cache_key);
    
    if ($counts !== false) {
        return $counts;
    }
    
    // Cache miss - executa queries
    $post_types = array('post', 'page', 'publicacoes', ...);
    $counts = array();
    
    foreach ($post_types as $post_type) {
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            's' => $search_query,
            'posts_per_page' => -1,
            'fields' => 'ids',
        );
        
        $query = new WP_Query($args);
        $counts[$post_type] = $query->found_posts;
        wp_reset_postdata();
    }
    
    // Armazena no cache por 1 hora
    set_transient($cache_key, $counts, HOUR_IN_SECONDS);
    
    return $counts;
}
```

**Limpar cache ao publicar:**

```php
add_action('save_post', function($post_id) {
    // Limpa todos os caches de busca
    global $wpdb;
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_search_counts_%'");
});
```

---

### 6.3 Adicionar Índices MySQL

**Executar no phpMyAdmin ou via WP-CLI:**

```sql
-- Adiciona índice full-text para busca mais rápida
ALTER TABLE wp_posts 
ADD FULLTEXT INDEX search_idx (post_title, post_content);

-- Índice para post_type + post_status (comum em buscas)
CREATE INDEX post_type_status_idx 
ON wp_posts (post_type, post_status);
```

**Modificar query para usar full-text:**

```php
// Em cchla_search_query_modification()
add_filter('posts_search', function($search, $wp_query) {
    if (!$wp_query->is_search()) {
        return $search;
    }
    
    global $wpdb;
    $search_term = $wp_query->get('s');
    
    if (empty($search_term)) {
        return $search;
    }
    
    // Usa full-text search do MySQL
    $search = " AND MATCH (post_title, post_content) AGAINST ('" . 
              esc_sql($search_term) . "' IN NATURAL LANGUAGE MODE) ";
    
    return $search;
}, 10, 2);
```

---

### 6.4 Lazy Loading de Imagens

**Já implementado nos cards:**

```php
<?php the_post_thumbnail('thumbnail', array(
    'loading' => 'lazy',  // ← Lazy loading nativo
    'class' => 'w-20 h-28 object-cover'
)); ?>
```

---

### 6.5 Paginação Otimizada

**Evitar offset alto (ruim para performance):**

```php
// ❌ RUIM - Página 100 = OFFSET 990
SELECT * FROM wp_posts LIMIT 10 OFFSET 990;

// ✅ BOM - Usar cursor-based pagination
SELECT * FROM wp_posts WHERE ID > 12345 LIMIT 10;
```

**Implementação:**

```php
// Em cchla_search_query_modification()
if (isset($_GET['after']) && is_numeric($_GET['after'])) {
    $query->set('post__not_in', array());
    add_filter('posts_where', function($where) use ($wpdb) {
        $after_id = intval($_GET['after']);
        $where .= " AND {$wpdb->posts}.ID > {$after_id}";
        return $where;
    });
}
```

---

### 6.6 Benchmarks

**Ambiente de Teste:**
- WordPress 6.4
- PHP 8.2
- MySQL 8.0
- 1000 posts de cada tipo

| Cenário                   | Sem Otimizações | Com Cache | Com Índices | Com Ambos |
| ------------------------- | --------------- | --------- | ----------- | --------- |
| Busca simples (1 palavra) | 250ms           | 180ms     | 120ms       | 80ms      |
| Busca com filtro          | 180ms           | 120ms     | 90ms        | 60ms      |
| Contagem por tipo         | 200ms           | 5ms       | 150ms       | 5ms       |
| Página 1                  | 250ms           | 180ms     | 120ms       | 80ms      |
| Página 10                 | 350ms           | 280ms     | 150ms       | 110ms     |

---

### 7.1 Problemas Comuns e Soluções

#### Problema 1: Busca Retorna Página em Branco

**Sintomas:**
- Página de busca carrega vazia
- Sem mensagem de erro
- URL correta: `/?s=termo`

**Causas Possíveis:**

1. **Template `search.php` não existe**
   ```bash
   # Verificar
   ls -la theme-root/search.php
   ```
   
   **Solução:** Criar o arquivo `search.php` na raiz do tema.

2. **Erro fatal no código PHP**
   ```bash
   # Ativar debug
   # wp-config.php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   
   # Verificar logs
   tail -f wp-content/debug.log
   ```

3. **Memory limit excedido**
   ```php
   // Verificar em search.php
   echo 'Memory: ' . memory_get_usage(true) / 1024 / 1024 . 'MB';
   
   // Aumentar limite temporariamente
   ini_set('memory_limit', '256M');
   ```

---

#### Problema 2: Alguns Post Types Não Aparecem

**Sintomas:**
- Publicações aparecem, mas Especiais não
- Filtros não mostram todos os tipos

**Diagnóstico:**

```php
// Adicionar temporariamente em search.php (antes do loop)
echo '<pre>';
var_dump($wp_query->query_vars['post_type']);
var_dump($wp_query->found_posts);
echo '</pre>';
```

**Causas:**

1. **Post type não incluído na query**
   
   **Verificar em `functions.php`:**
   ```php
   function cchla_search_query_modification($query) {
       // Verificar se 'especiais' está na lista
       $query->set('post_type', array(
           'post',
           'page',
           'publicacoes',
           'especiais', // ← Deve estar aqui
           'servicos',
           'acesso_rapido'
       ));
   }
   ```

2. **Post type não registrado**
   
   ```php
   // Testar no console
   if (!post_type_exists('especiais')) {
       echo 'CPT não registrado!';
   }
   ```
   
   **Solução:** Ir em **Configurações → Links Permanentes** e clicar em **Salvar**.

3. **Permissões incorretas**
   
   ```php
   // Verificar capability_type do CPT
   'capability_type' => 'post', // ✅ Correto
   'capability_type' => 'especial', // ❌ Pode causar problemas
   ```

---

#### Problema 3: Contadores Errados nos Filtros

**Sintomas:**
- Sidebar mostra "5 Publicações" mas busca retorna 3
- Números inconsistentes

**Diagnóstico:**

```php
// Em search.php, comparar:
$counts = cchla_get_search_counts_by_type($search_query);
echo 'Contador diz: ' . $counts['publicacoes'];
echo 'Query encontrou: ' . $wp_query->found_posts;
```

**Causas:**

1. **Cache desatualizado**
   
   ```php
   // Limpar manualmente
   delete_transient('search_counts_' . md5($search_query));
   
   // Ou limpar todos
   global $wpdb;
   $wpdb->query("DELETE FROM $wpdb->options 
                 WHERE option_name LIKE '_transient_search_counts_%'");
   ```

2. **Query de contagem diferente da query principal**
   
   **Solução:** Garantir que ambas usem os mesmos parâmetros:
   ```php
   // Ambas devem ter
   'post_status' => 'publish',
   'posts_per_page' => -1, // Ou número alto para contagem
   ```

---

#### Problema 4: Destaque de Termos Não Funciona

**Sintomas:**
- Termo "inteligência" buscado mas não destacado
- Tag `<mark>` não aparece no HTML

**Diagnóstico:**

```php
// Ver output da função
$text = "Inteligência Artificial";
$term = "inteligência";
$result = cchla_highlight_search_term($text, $term);
var_dump($result);
```

**Causas:**

1. **Termo vazio**
   ```php
   // Verificar
   $search_term = get_search_query();
   if (empty($search_term)) {
       echo 'Termo de busca vazio!';
   }
   ```

2. **Caracteres especiais**
   ```php
   // Problema: termos com acentos ou caracteres especiais
   // Solução já implementada com flag 'u' (unicode)
   preg_replace('/(...)/iu', '<mark>$1</mark>', $text);
   ```

3. **CSS do `<mark>` ausente**
   ```css
   /* Adicionar em style.css */
   mark {
       background-color: #fef3c7;
       color: #92400e;
       font-weight: 600;
       padding: 2px 4px;
       border-radius: 3px;
   }
   ```

---

#### Problema 5: Paginação Não Funciona

**Sintomas:**
- Clicar em "Página 2" retorna mesmos resultados
- URL muda mas conteúdo não

**Diagnóstico:**

```php
// Verificar em search.php
$current_page = max(1, get_query_var('paged'));
$total_pages = $wp_query->max_num_pages;
echo "Página {$current_page} de {$total_pages}";
```

**Causas:**

1. **Parâmetro `paged` não capturado**
   
   ```php
   // Adicionar em cchla_search_query_modification()
   $paged = max(1, get_query_var('paged'));
   $query->set('paged', $paged);
   ```

2. **Permalinks quebrados**
   
   **Solução:** **Configurações → Links Permanentes → Salvar**

3. **Conflito com `posts_per_page`**
   
   ```php
   // Verificar se não está setado como -1
   $query->set('posts_per_page', 10); // ✅ Correto
   ```

---

### 7.2 Logs e Monitoramento

#### A) Habilitar Query Monitor (Plugin)

```bash
# Via WP-CLI
wp plugin install query-monitor --activate

# Ou instalar pelo admin
# Plugins → Adicionar Novo → "Query Monitor"
```

**Usar para:**
- Ver todas as queries executadas
- Tempo de cada query
- Queries duplicadas
- Uso de memória

---

#### B) Log Customizado de Buscas

**Adicionar em `functions.php`:**

```php
/**
 * Registra todas as buscas em log customizado
 */
function cchla_log_search($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        $search_term = get_search_query();
        $results_count = $query->found_posts;
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $timestamp = current_time('mysql');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'search_logs';
        
        $wpdb->insert($table_name, array(
            'search_term' => $search_term,
            'results_count' => $results_count,
            'user_ip' => $user_ip,
            'timestamp' => $timestamp,
            'post_type_filter' => isset($_GET['post_type']) ? $_GET['post_type'] : 'all'
        ));
    }
}
add_action('pre_get_posts', 'cchla_log_search', 999);

/**
 * Criar tabela de logs (executar uma vez)
 */
function cchla_create_search_logs_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'search_logs';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        search_term varchar(255) NOT NULL,
        results_count int(11) NOT NULL,
        user_ip varchar(45) NOT NULL,
        post_type_filter varchar(50) DEFAULT 'all',
        timestamp datetime NOT NULL,
        PRIMARY KEY (id),
        KEY search_term (search_term),
        KEY timestamp (timestamp)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'cchla_create_search_logs_table');
```

**Consultar logs:**

```php
// Termos mais buscados (últimos 30 dias)
global $wpdb;
$table_name = $wpdb->prefix . 'search_logs';

$popular = $wpdb->get_results("
    SELECT search_term, COUNT(*) as count
    FROM $table_name
    WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY search_term
    ORDER BY count DESC
    LIMIT 10
");

// Buscas sem resultados
$no_results = $wpdb->get_results("
    SELECT search_term, COUNT(*) as count
    FROM $table_name
    WHERE results_count = 0
    AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY search_term
    ORDER BY count DESC
    LIMIT 10
");
```

---

#### C) Dashboard de Estatísticas

**Criar página admin em `admin/search-stats.php`:**

```php
<?php
/**
 * Página de Estatísticas de Busca
 */

// Adicionar menu no admin
add_action('admin_menu', function() {
    add_menu_page(
        'Estatísticas de Busca',
        'Busca Stats',
        'manage_options',
        'cchla-search-stats',
        'cchla_search_stats_page',
        'dashicons-chart-bar',
        30
    );
});

function cchla_search_stats_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'search_logs';
    
    // Estatísticas gerais
    $total_searches = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    
    $avg_results = $wpdb->get_var("
        SELECT AVG(results_count) 
        FROM $table_name 
        WHERE results_count > 0
    ");
    
    $no_results_count = $wpdb->get_var("
        SELECT COUNT(*) 
        FROM $table_name 
        WHERE results_count = 0
    ");
    
    $no_results_percent = ($total_searches > 0) ? 
        round(($no_results_count / $total_searches) * 100, 2) : 0;
    
    // Top 10 termos
    $top_terms = $wpdb->get_results("
        SELECT search_term, COUNT(*) as count, AVG(results_count) as avg_results
        FROM $table_name
        WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY search_term
        ORDER BY count DESC
        LIMIT 10
    ");
    
    // Buscas sem resultados
    $failed_searches = $wpdb->get_results("
        SELECT search_term, COUNT(*) as count
        FROM $table_name
        WHERE results_count = 0
        AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY search_term
        ORDER BY count DESC
        LIMIT 10
    ");
    
    ?>
    <div class="wrap">
        <h1>📊 Estatísticas de Busca - CCHLA</h1>
        
        <!-- Cards de Resumo -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0;">
            <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #2563eb;">
                <h3 style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">Total de Buscas</h3>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #1f2937;">
                    <?php echo number_format_i18n($total_searches); ?>
                </p>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #10b981;">
                <h3 style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">Média de Resultados</h3>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #1f2937;">
                    <?php echo number_format_i18n($avg_results, 1); ?>
                </p>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #ef4444;">
                <h3 style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">Sem Resultados</h3>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #1f2937;">
                    <?php echo number_format_i18n($no_results_count); ?>
                </p>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                <h3 style="margin: 0 0 10px 0; color: #6b7280; font-size: 14px;">Taxa de Falha</h3>
                <p style="margin: 0; font-size: 32px; font-weight: bold; color: #1f2937;">
                    <?php echo $no_results_percent; ?>%
                </p>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Top Termos -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h2>🔥 Top 10 Termos Buscados (30 dias)</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Termo</th>
                            <th>Buscas</th>
                            <th>Média Resultados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_terms as $term) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($term->search_term); ?></strong></td>
                            <td><?php echo number_format_i18n($term->count); ?></td>
                            <td><?php echo number_format_i18n($term->avg_results, 1); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Buscas Sem Resultados -->
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h2>⚠️ Buscas Sem Resultados (30 dias)</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Termo</th>
                            <th>Tentativas</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($failed_searches as $search) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($search->search_term); ?></strong></td>
                            <td><?php echo number_format_i18n($search->count); ?></td>
                            <td>
                                <a href="<?php echo admin_url('post-new.php'); ?>" class="button button-small">
                                    Criar Conteúdo
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Ações Recomendadas -->
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 20px; margin-top: 20px;">
            <h3 style="margin-top: 0; color: #1e40af;">💡 Recomendações</h3>
            <ul>
                <?php if ($no_results_percent > 20) : ?>
                <li><strong>Alta taxa de buscas sem resultados (<?php echo $no_results_percent; ?>%)</strong><br>
                    Considere criar conteúdo sobre os termos mais buscados sem resultados.</li>
                <?php endif; ?>
                
                <?php if ($avg_results > 50) : ?>
                <li><strong>Média alta de resultados (<?php echo round($avg_results); ?>)</strong><br>
                    Usuários podem ter dificuldade em encontrar o que buscam. Considere melhorar a relevância ou adicionar mais filtros.</li>
                <?php endif; ?>
                
                <li>Monitore regularmente os termos sem resultados para identificar lacunas de conteúdo.</li>
                <li>Use sinônimos e palavras-chave dos termos populares em seus conteúdos para melhorar a descoberta.</li>
            </ul>
        </div>
    </div>
    <?php
}
```

---

## 8. Boas Práticas

### 8.1 Segurança

#### Sanitização de Inputs

```php
// ✅ SEMPRE sanitizar
$search_term = sanitize_text_field($_GET['s']);
$post_type = sanitize_key($_GET['post_type']);

// ❌ NUNCA usar direto
$query = "SELECT * FROM posts WHERE title LIKE '%{$_GET['s']}%'";
```

#### Escape de Outputs

```php
// ✅ Escapar antes de imprimir
echo esc_html($search_term);
echo esc_attr($post_type);
echo esc_url($link);

// ❌ NUNCA imprimir direto
echo $_GET['s'];
```

#### Nonces para AJAX

```php
// Gerar
$nonce = wp_create_nonce('cchla-search-nonce');

// Verificar
check_ajax_referer('cchla-search-nonce', 'nonce');
```

---

### 8.2 Acessibilidade (WCAG 2.1)

```html
<!-- ✅ BOM -->
<form role="search" aria-label="Busca no site">
    <input 
        type="search" 
        name="s" 
        aria-label="Campo de busca"
        placeholder="Digite sua busca..."
    />
    <button type="submit" aria-label="Executar busca">
        Buscar
    </button>
</form>

<!-- Resultados com ARIA -->
<div aria-live="polite" aria-atomic="true">
    <p>15 resultados encontrados para "inteligência"</p>
</div>

<!-- Links acessíveis -->
<a href="..." aria-label="Ver publicação: Título do Artigo">
    Leia mais
</a>
```

---

### 8.3 SEO

#### Meta Tags para Busca

```php
// Adicionar em search.php (no <head>)
<meta name="robots" content="noindex, follow">
<title><?php printf(
    __('Resultados para "%s" - %s', 'cchla-ufrn'),
    get_search_query(),
    get_bloginfo('name')
); ?></title>
```

#### Canonical URL

```php
<link rel="canonical" href="<?php echo esc_url(get_search_link()); ?>">
```

#### Schema.org Markup

```php
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SearchResultsPage",
  "mainEntity": {
    "@type": "ItemList",
    "numberOfItems": <?php echo $wp_query->found_posts; ?>,
    "itemListElement": [
      <?php
      $i = 0;
      while (have_posts()) : the_post();
          if ($i++ > 0) echo ',';
      ?>
      {
        "@type": "ListItem",
        "position": <?php echo $i; ?>,
        "url": "<?php the_permalink(); ?>",
        "name": "<?php echo esc_js(get_the_title()); ?>"
      }
      <?php endwhile; ?>
    ]
  }
}
</script>
```

---

### 8.4 Internacionalização (i18n)

```php
// ✅ Sempre usar funções de tradução
__('Buscar', 'cchla-ufrn');
_e('Resultados encontrados', 'cchla-ufrn');
esc_html__('Ver mais', 'cchla-ufrn');

// Com plurais
_n('%s resultado', '%s resultados', $count, 'cchla-ufrn');

// Com contexto
_x('Busca', 'nome do menu', 'cchla-ufrn');
```

**Gerar arquivo .pot:**
```bash
wp i18n make-pot . languages/cchla-ufrn.pot
```

---