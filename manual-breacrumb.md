# 📚 Manual Completo do Breadcrumb CCHLA

## Índice
1. [O que é Breadcrumb?](#o-que-é-breadcrumb)
2. [Como Funciona no Site CCHLA](#como-funciona-no-site-cchla)
3. [Formas de Usar](#formas-de-usar)
4. [Exemplos Práticos](#exemplos-práticos)
5. [Personalização](#personalização)
6. [Resolução de Problemas](#resolução-de-problemas)
7. [FAQ](#faq)

---

## O que é Breadcrumb?

O **breadcrumb** (migalha de pão) é um elemento de navegação que mostra ao usuário onde ele está no site e permite voltar facilmente para páginas anteriores.

### Exemplo Visual:
```
Início › Publicações › Livros › Nome do Livro
  ↑          ↑           ↑            ↑
 Home    Arquivo     Categoria    Página Atual
```

### Por que usar?
✅ **Melhora a navegação** - Usuário sabe onde está  
✅ **Melhora o SEO** - Google entende a estrutura do site  
✅ **Reduz taxa de rejeição** - Fácil voltar para outras páginas  
✅ **Acessibilidade** - Leitores de tela entendem a hierarquia  

---

## Como Funciona no Site CCHLA

O breadcrumb aparece automaticamente em todas as páginas (exceto a home) e se adapta ao tipo de conteúdo:

### Estrutura por Tipo de Conteúdo

#### 📰 **Notícias (Posts Padrão)**
```
Início › Destaque › Título da Notícia
Início › Outros Destaques › Nova bolsa de estudos
```

#### 📚 **Publicações**
```
Início › Publicações › Título
Início › Publicações › E-book › Nome do E-book
Início › Publicações › Livro › Título do Livro
```

#### 🎬 **Especiais**
```
Início › Especiais › Título
Início › Especiais › Comunicação › Nome do Projeto
Início › Especiais › Educação › Projeto Educacional
```

#### 💼 **Serviços**
```
Início › Serviços › Título
Início › Serviços › Extensão › Nome do Serviço
Início › Serviços › Cultura › Atividade Cultural
```

#### 🔗 **Sistemas (Acesso Rápido)**
```
Início › Sistemas › Nome do Sistema
Início › Sistemas › UFRN › SIGAA
Início › Sistemas › Externos › Sistema Externo
```

#### 📄 **Páginas**
```
Início › Sobre › História
Início › Sobre › História › Linha do Tempo
Início › Departamentos › Filosofia
```

---

## Formas de Usar

### 1️⃣ **Método Recomendado: Função PHP**

#### Uso Básico
```php
<?php cchla_breadcrumb(); ?>
```

#### Onde colocar:
- No início do `single.php`
- No início do `page.php`
- No início do `archive.php`
- Em qualquer template após `get_header()`

**Exemplo completo:**
```php
<?php get_header(); ?>

<?php cchla_breadcrumb(); ?>

<main class="container mx-auto px-4 py-8">
    <!-- Seu conteúdo aqui -->
</main>

<?php get_footer(); ?>
```

---

### 2️⃣ **Personalização com Parâmetros**

#### Mudar o texto "Início"
```php
<?php 
cchla_breadcrumb(array(
    'home_text' => 'Home'
)); 
?>
```

#### Mudar o separador
```php
<?php 
cchla_breadcrumb(array(
    'separator' => '/'
)); 
?>

// Resultado: Início / Publicações / Livros / Título
```

Outros separadores comuns:
- `›` (padrão)
- `/`
- `>`
- `→`
- `•`

#### Ocultar a página atual
```php
<?php 
cchla_breadcrumb(array(
    'show_current' => false
)); 
?>

// Resultado: Início › Publicações › Livros
// (sem mostrar o título da página atual)
```

#### Personalização completa
```php
<?php 
cchla_breadcrumb(array(
    'home_text' => 'Página Inicial',
    'separator' => '→',
    'show_current' => true
)); 
?>
```

---

### 3️⃣ **Usando como Shortcode**

Para adicionar breadcrumb **dentro de um post ou página** usando o editor:

#### No Editor Clássico
Cole este código no modo **Texto**:
```
[breadcrumb]
```

#### No Gutenberg (Blocos)
1. Adicione um bloco **Shortcode**
2. Digite: `[breadcrumb]`

#### Com parâmetros:
```
[breadcrumb home_text="Home" separator="/"]
```

---

### 4️⃣ **Retornar HTML (Avançado)**

Para armazenar o breadcrumb em uma variável:

```php
<?php 
$breadcrumb_html = cchla_breadcrumb(array('echo' => false));

// Agora você pode usar a variável
echo '<div class="meu-container">';
echo $breadcrumb_html;
echo '</div>';
?>
```

---

## Exemplos Práticos

### Exemplo 1: Single Post (Notícia)

**Arquivo:** `single.php`

```php
<?php
/**
 * Template para exibir posts individuais (notícias)
 */

get_header();
?>

<?php cchla_breadcrumb(); ?>

<article class="max-w-4xl mx-auto px-4 py-8">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        
        <header class="mb-8">
            <h1 class="text-4xl font-bold mb-4"><?php the_title(); ?></h1>
            
            <div class="flex items-center gap-4 text-sm text-gray-600">
                <time datetime="<?php echo get_the_date('c'); ?>">
                    <?php echo get_the_date(); ?>
                </time>
                <span>•</span>
                <span><?php the_author(); ?></span>
            </div>
        </header>

        <div class="prose max-w-none">
            <?php the_content(); ?>
        </div>
        
    <?php endwhile; ?>
</article>

<?php get_footer(); ?>
```

---

### Exemplo 2: Arquivo de Publicações

**Arquivo:** `archive-publicacoes.php`

```php
<?php
/**
 * Template para exibir arquivo de publicações
 */

get_header();
?>

<?php cchla_breadcrumb(array('separator' => '/')); ?>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <header class="mb-12 text-center">
        <h1 class="text-5xl font-bold text-gray-900 mb-4">
            📚 Publicações CCHLA
        </h1>
        <p class="text-xl text-gray-600">
            Explore nossa produção acadêmica
        </p>
    </header>

    <?php if (have_posts()) : ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php
            while (have_posts()) :
                the_post();
                get_template_part('template-parts/card', 'publicacao');
            endwhile;
            ?>
        </div>

        <?php
        // Paginação
        the_posts_pagination();
        ?>
    <?php else : ?>
        <p class="text-center text-gray-500">Nenhuma publicação encontrada.</p>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
```

---

### Exemplo 3: Página com Breadcrumb Personalizado

**Arquivo:** `page-sobre.php`

```php
<?php
/**
 * Template Name: Página Sobre
 */

get_header();
?>

<?php 
cchla_breadcrumb(array(
    'home_text' => 'Página Inicial',
    'separator' => '→',
)); 
?>

<div class="max-w-6xl mx-auto px-4 py-12">
    
    <?php while (have_posts()) : the_post(); ?>
        
        <header class="mb-12">
            <h1 class="text-5xl font-bold text-gray-900">
                <?php the_title(); ?>
            </h1>
        </header>

        <div class="prose prose-lg max-w-none">
            <?php the_content(); ?>
        </div>

    <?php endwhile; ?>

</div>

<?php get_footer(); ?>
```

---

### Exemplo 4: Taxonomia com Filtros

**Arquivo:** `taxonomy-tipo_publicacao.php`

```php
<?php
/**
 * Template para taxonomia Tipo de Publicação
 */

get_header();

$term = get_queried_object();
?>

<?php cchla_breadcrumb(); ?>

<div class="max-w-7xl mx-auto px-4 py-8">
    
    <header class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-2">
            <?php single_term_title(); ?>
        </h1>
        
        <?php if ($term->description) : ?>
            <p class="text-gray-600 text-lg">
                <?php echo $term->description; ?>
            </p>
        <?php endif; ?>
    </header>

    <?php if (have_posts()) : ?>
        <div class="grid md:grid-cols-3 gap-6">
            <?php
            while (have_posts()) :
                the_post();
                get_template_part('template-parts/card', 'publicacao');
            endwhile;
            ?>
        </div>
    <?php endif; ?>

</div>

<?php get_footer(); ?>
```

---

## Personalização Avançada

### Modificar o CSS do Breadcrumb

O breadcrumb usa classes Tailwind CSS. Para personalizar:

**Opção 1: Adicionar CSS customizado**

No seu arquivo CSS:
```css
/* Mudar cor de fundo */
nav[aria-label="breadcrumb"] {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
}

/* Mudar cor dos links */
nav[aria-label="breadcrumb"] a {
    color: #0066cc !important;
}

/* Mudar cor do separador */
nav[aria-label="breadcrumb"] .text-gray-400 {
    color: #999 !important;
}

/* Aumentar tamanho da fonte */
nav[aria-label="breadcrumb"] ol {
    font-size: 16px !important;
}
```

**Opção 2: Modificar o template**

Edite o arquivo: `parts/extra/template-parts/breadcrumb.php`

Altere as classes na linha:
```php
<nav class="bg-gray-100 border-b border-gray-300" aria-label="breadcrumb">
```

Para:
```php
<nav class="bg-blue-50 border-b border-blue-200" aria-label="breadcrumb">
```

---

### Criar Template Personalizado

Se você precisa de um breadcrumb completamente diferente, crie um novo template:

**1. Duplique o arquivo:**
```
parts/extra/template-parts/breadcrumb.php
↓
parts/extra/template-parts/breadcrumb-custom.php
```

**2. Modifique conforme necessário**

**3. Chame o template personalizado:**
```php
<?php get_template_part('parts/extra/template-parts/breadcrumb-custom'); ?>
```

---

## Resolução de Problemas

### ❌ Problema 1: Breadcrumb não aparece

**Causa:** Está na página inicial (home)  
**Solução:** O breadcrumb não aparece na home por padrão. Isso é intencional.

---

### ❌ Problema 2: Mostra "Início › Título" sem categoria

**Causa:** O post não tem categoria atribuída  
**Solução:** 
1. Vá em **Posts → Categorias**
2. Crie ou atribua uma categoria ao post
3. O breadcrumb será: `Início › Nome da Categoria › Título`

---

### ❌ Problema 3: Erro "Call to undefined function"

**Causa:** A função `cchla_breadcrumb()` não foi adicionada ao `functions.php`

**Solução:**
1. Abra `functions.php`
2. Adicione o código da função fornecido no manual de instalação
3. Salve o arquivo

---

### ❌ Problema 4: Breadcrumb aparece duas vezes

**Causa:** Você chamou `cchla_breadcrumb()` E também usou `get_template_part()` no mesmo template

**Solução:** Use apenas UMA das opções:
```php
// CORRETO - Escolha uma:
<?php cchla_breadcrumb(); ?>

// OU

<?php get_template_part('parts/extra/template-parts/breadcrumb'); ?>

// ERRADO - Não use as duas juntas!
```

---

### ❌ Problema 5: Separador não muda

**Causa:** O parâmetro está sendo passado incorretamente

**Solução:** Verifique a sintaxe:
```php
// CORRETO
<?php cchla_breadcrumb(array('separator' => '/')); ?>

// ERRADO
<?php cchla_breadcrumb('separator' => '/'); ?>
```

---

### ❌ Problema 6: Ícones não aparecem

**Causa:** Font Awesome não está carregado

**Solução:** Adicione no `functions.php`:
```php
function cchla_enqueue_fontawesome() {
    wp_enqueue_style(
        'font-awesome', 
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'
    );
}
add_action('wp_enqueue_scripts', 'cchla_enqueue_fontawesome');
```

---

## FAQ (Perguntas Frequentes)

### 1. Posso usar breadcrumb em widgets?

✅ **Sim!** Use o shortcode:
```
[breadcrumb]
```

No widget de **Texto** ou **HTML Personalizado**.

---

### 2. Como adicionar breadcrumb em todos os templates automaticamente?

Adicione no `functions.php`:

```php
function cchla_auto_breadcrumb() {
    if (!is_front_page() && (is_singular() || is_archive())) {
        cchla_breadcrumb();
    }
}
add_action('cchla_before_content', 'cchla_auto_breadcrumb');
```

E nos seus templates, adicione:
```php
<?php do_action('cchla_before_content'); ?>
```

---

### 3. Como mudar a ordem dos itens?

A ordem é automática e segue a hierarquia:
```
Home → Arquivo → Taxonomia → Post
```

Para mudar, você precisa editar o template `breadcrumb.php`.

---

### 4. Posso usar emojis como separador?

✅ **Sim!**
```php
<?php cchla_breadcrumb(array('separator' => '🔹')); ?>
```

---

### 5. Como desabilitar breadcrumb em páginas específicas?

```php
<?php 
if (!is_page('contato')) {
    cchla_breadcrumb();
}
?>
```

Ou:
```php
<?php 
if (!is_singular('servicos')) {
    cchla_breadcrumb();
}
?>
```

---

### 6. O breadcrumb é bom para SEO?

✅ **Sim!** O breadcrumb:
- Ajuda o Google a entender a estrutura do site
- Aparece nos resultados de busca
- Melhora a experiência do usuário
- Reduz a taxa de rejeição

---

### 7. Posso traduzir o breadcrumb?

✅ **Sim!** O breadcrumb usa funções de tradução do WordPress:

```php
__('Início', 'cchla-ufrn')
```

Você pode criar arquivos de tradução `.po/.mo` para outros idiomas.

---

### 8. Como adicionar Schema.org ao breadcrumb?

O breadcrumb atual usa marcação semântica HTML5. Para adicionar Schema.org, modifique o template adicionando:

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [...]
}
</script>
```

---

## Checklist de Implementação

Use esta lista para garantir que tudo está funcionando:

- [ ] Função `cchla_breadcrumb()` adicionada ao `functions.php`
- [ ] Template `breadcrumb.php` na pasta correta
- [ ] Font Awesome carregado no site
- [ ] Breadcrumb adicionado nos templates principais
- [ ] Testado em posts de todos os tipos (notícias, publicações, especiais, etc)
- [ ] Testado em arquivos (listas de posts)
- [ ] Testado em taxonomias (categorias, tipos, etc)
- [ ] Testado em páginas simples e hierárquicas
- [ ] Testado no mobile (responsividade)
- [ ] Validado HTML (sem erros)
- [ ] Testado acessibilidade (leitores de tela)

---

**Manual criado para o tema CCHLA-UFRN**  
Versão 1.0 - Atualizado em 2025