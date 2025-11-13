# 🚀 Guia de Instalação e Configuração - CCHLA UFRN

Manual completo para colocar o site institucional do CCHLA no ar.

---

## 📋 Índice

1. [Requisitos do Servidor](#-requisitos-do-servidor)
2. [Instalação do WordPress](#-instalação-do-wordpress)
3. [Instalação do Tema CCHLA](#-instalação-do-tema-cchla)
4. [Configuração Inicial](#-configuração-inicial)
5. [Criação de Menus](#-criação-de-menus)
6. [Configuração de Páginas](#-configuração-de-páginas)
7. [Informações de Contato](#-informações-de-contato)
8. [Redes Sociais](#-redes-sociais)
9. [Publicação de Conteúdo](#-publicação-de-conteúdo)
10. [Segurança e Performance](#-segurança-e-performance)
11. [Problemas Comuns](#-problemas-comuns)

---

## 🖥️ Requisitos do Servidor

### **Requisitos Mínimos**

```
✅ PHP 8.0 ou superior
✅ MySQL 5.7+ ou MariaDB 10.3+
✅ Apache 2.4+ ou Nginx 1.18+
✅ HTTPS configurado (SSL)
✅ 512 MB de RAM (recomendado: 1 GB)
✅ 1 GB de espaço em disco
```

### **Extensões PHP Necessárias**

```
✅ php-curl
✅ php-gd
✅ php-mbstring
✅ php-xml
✅ php-zip
✅ php-mysql
✅ php-imagick (opcional, mas recomendado)
```

### **Verificar Requisitos**

Crie um arquivo `info.php` na raiz do servidor:

```php
<?php phpinfo(); ?>
```

Acesse: `https://cchla.ufrn.br/info.php`

**⚠️ IMPORTANTE:** Delete este arquivo após verificar!


## Instalação do WordPress

Comece por aqui se você ainda não tem uma instalação do Wordpress. Neste caso, vamos apresentar um processo de instalação manual.

### **Opção 1: Instalação Manual**

Primeira opção é que você irá configurar tudo manualmente.

#### **1. Baixar WordPress**

```bash
cd /var/www/html
wget https://br.wordpress.org/latest-pt_BR.zip
unzip latest-pt_BR.zip
mv wordpress/* .
rm -rf wordpress latest-pt_BR.zip
```

#### **2. Criar Banco de Dados**

```sql
CREATE DATABASE cchla_ufrn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cchla_user'@'localhost' IDENTIFIED BY 'senha_segura_aqui';
GRANT ALL PRIVILEGES ON cchla_ufrn.* TO 'cchla_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### **3. Configurar wp-config.php**

```bash
cp wp-config-sample.php wp-config.php
nano wp-config.php
```

Edite as seguintes linhas:

```php
define( 'DB_NAME', 'cchla_ufrn' );
define( 'DB_USER', 'cchla_user' );
define( 'DB_PASSWORD', 'senha_segura_aqui' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', 'utf8mb4_unicode_ci' );
```

#### **4. Gerar Chaves de Segurança**

Acesse: https://api.wordpress.org/secret-key/1.1/salt/

Copie e cole as chaves no `wp-config.php`

#### **5. Definir Permissões**

```bash
chown -R www-data:www-data /var/www/html
find /var/www/html -type d -exec chmod 755 {} \;
find /var/www/html -type f -exec chmod 644 {} \;
```

#### **6. Instalar WordPress**

Acesse: `https://cchla.ufrn.br/wp-admin/install.php`

Preencha:
- **Título do Site:** CCHLA - UFRN
- **Nome de Usuário:** admin (ou outro seguro)
- **Senha:** (use senha forte!)
- **E-mail:** seu-email@[ufrn].br

Clique em **"Instalar WordPress"**

### **Opção 2: Instalação Guiada**


#### **1. Baixar WordPress**
Para isto, baixar seguir o passo 1.1

#### **2. Criar Banco de Dados**
Siga o passo 1.2. Sim, você precisará criar um banco de dados para o seu site.

#### **3. Instalar WordPress**

Acesse o link do seu site: `https://cchla.ufrn.br/` e basta seguir o passo a passo.



## Instalação do Tema CCHLA

Comece daqui se a etapa um já está completa. Você pode ter começado de uma instalação pronta, então o passo anterior não é mais necessário. Se esse é o seu caso, comece por esta seção.

### **1. Upload do Tema**

#### **Via Admin (Recomendado)**

1. Acesse: `https://cchla.ufrn.br/wp-admin`
2. Menu: **Aparência → Temas**
3. Clique em **"Adicionar Novo"**
4. Clique em **"Enviar Tema"**
5. Selecione o arquivo: `cchla-ufrn.zip`
6. Clique em **"Instalar Agora"**
7. Clique em **"Ativar"**

#### **Via FTP/SSH**

```bash
cd /var/www/html/wp-content/themes/
unzip cchla-ufrn.zip
chown -R www-data:www-data cchla-ufrn
```

Depois ative no admin: **Aparência → Temas → CCHLA UFRN → Ativar**

### **2. Instalar Plugins Recomendados**

Acesse: **Plugins → Adicionar Novo**

Instale e ative:

```
✅ Classic Editor (se preferir o editor clássico)
✅ Yoast SEO (otimização para mecanismos de busca)
✅ Wordfence Security (segurança)
✅ WP Super Cache (cache e performance)
✅ Contact Form 7 (formulários de contato)
```



## ⚙️ Configuração Inicial

### **1. Configurações Gerais**

**Menu:** `Configurações → Geral`

```
Título do site: CCHLA - Centro de Ciências Humanas, Letras e Artes
Slogan: Universidade Federal do Rio Grande do Norte
URL do WordPress: https://cchla.ufrn.br
URL do Site: https://cchla.ufrn.br
Endereço de E-mail: contato@cchla.ufrn.br
Fuso horário: São Paulo (UTC-3)
Formato de data: d/m/Y
Formato de hora: H:i
```

Clique em **"Salvar Alterações"**

### **2. Configurações de Leitura**

**Menu:** `Configurações → Leitura`

```
Sua página inicial exibe:
  ○ Suas últimas publicações
  ● Uma página estática (selecione abaixo)
  
Página inicial: Início
Página de posts: Blog

As páginas do site devem mostrar no máximo: 10 posts

```

Clique em **"Salvar Alterações"**

### **3. Configurações de Links Permanentes**

**Menu:** `Configurações → Links Permanentes`

Selecione: **`● Nome do post`**

Estrutura personalizada: `/%category%/%postname%/`

Clique em **"Salvar Alterações"**

### **4. Configurações de Mídia**

**Menu:** `Configurações → Mídia`

```
Tamanho da miniatura:
  Largura máxima: 300
  Altura máxima: 300
  ☑ Cortar a miniatura

Tamanho médio:
  Largura máxima: 768
  Altura máxima: 0

Tamanho grande:
  Largura máxima: 1200
  Altura máxima: 0

```

Clique em **"Salvar Alterações"**



## 📋 Criação de Menus

### **1. Menu Principal (Cabeçalho)**

**Menu:** `Aparência → Menus`

#### **Criar novo menu:**

1. Clique em **"Criar um novo menu"**
2. **Nome do menu:** `Menu Principal`
3. Clique em **"Criar Menu"**

#### **Adicionar itens:**
> o menu abaixo é uma sugestão. Contudo, o procedimento ideal é criar uma dinâmica de card sorting para identificar o melhor menu.
> 
Na coluna esquerda, adicione páginas/links:

```
📁 Institucional
  └── História
  └── Administração
  └── Documentos
  └── CONSEC
📁 Departamentos
  └── DCS (Ciências Sociais)
  └── DFIL (Filosofia)
  └── DGEO (Geografia)
  └── DHIST (História)
  └── DLEM (Letras Modernas)
  └── DLLE (Letras Estrangeiras)
  └── DLPO (Língua Portuguesa)
  └── DPSI (Psicologia)
📁 Cursos
  └── Graduação
  └── Pós-Graduação
📁 Pesquisa
  └── Grupos de Pesquisa
  └── Projetos
📁 Extensão
  └── Programas
  └── Projetos
  └── Eventos
📄 Publicações
📄 Notícias
📄 Contato
```

### 2. Menu do Footer (Mapa do Site)

O footer utiliza um **único menu hierárquico** para organizar o mapa do site.

**Como configurar:**

1. Acesse: `Aparência → Menus`
2. Crie um menu chamado "Mapa do Site"
3. Estrutura:
   - **Itens Pais** (em MAIÚSCULAS) = Títulos das colunas
   - **Itens Filhos** (identados) = Links dentro da coluna
4. Atribua à localização: "Mapa do Site (Footer)"
5. Salve

**Exemplo:**
```
INSTITUCIONAL (Link: #)
  ├─ Administração
  ├─ Documentos
  └─ CONSEC

ACADÊMICO (Link: #)
  ├─ Ensino
  └─ Pesquisa
```

**Resultado:** Cada item pai vira uma coluna no footer.

#### **Configurar localização:**

Marque: `☑ Menu Principal`

Clique em **"Salvar Menu"**

---

## 📄 Configuração de Páginas

### **Páginas Essenciais**

Crie estas páginas em: `Páginas → Adicionar Nova`

#### **1. Página Inicial**
```
Título: Início
Conteúdo: (será preenchido com blocos/widgets)
Modelo: Página Inicial (se disponível)
```

#### **2. Blog/Notícias**
```
Título: Blog
Conteúdo: (deixe vazio - será preenchido automaticamente)
```

#### **3. Sobre**
```
Título: Institucional
Conteúdo: Informações sobre o CCHLA, história, missão, visão, valores...
```

#### **4. Contato**
```
Título: Contato
Conteúdo: 
- Formulário de contato (use Contact Form 7)
- Endereço
- Telefones
- Email
- Mapa incorporado (Google Maps)
```

#### **5. Política de Privacidade**
```
Título: Política de Privacidade
Conteúdo: Conforme LGPD
```

**Menu:** `Configurações → Privacidade` → Selecione esta página

---

## 📞 Informações de Contato

### **Configurar via Customizer**

**Menu:** `Aparência → Personalizar → Informações de Contato`

Preencha:

```
📍 Endereço:
Av. Sen. Salgado Filho, S/N – Lagoa Nova. Natal – RN, 59078-970

📞 Telefone Principal:
(84) 3342-2243

📞 Telefone Secundário:
(84) 99193-6154

📧 E-mail Principal:
secretariacchla@gmail.com

📧 E-mail Secundário:
(opcional)
```

Clique em **"Publicar"**

---

## 🌐 Redes Sociais

### **Configurar via Customizer**

**Menu:** `Aparência → Personalizar → Redes Sociais`

Adicione as URLs completas:

```
🐦 Twitter/X:
https://twitter.com/cchla_ufrn

📷 Instagram:
https://instagram.com/cchla.ufrn

🎥 YouTube:
https://youtube.com/@cchlaufrn

📘 Facebook:
https://facebook.com/cchlaufrn

💼 LinkedIn:
https://linkedin.com/company/cchla-ufrn

📱 WhatsApp (opcional):
5584999136154
(apenas números, será convertido automaticamente)
```

**⚠️ Deixe em branco** as redes que não possuir - elas não aparecerão no site.

Clique em **"Publicar"**

---

## 📝 Publicação de Conteúdo

### **1. Posts (Notícias)**

**Menu:** `Posts → Adicionar Novo`

```
Título: [Título da notícia]

Conteúdo:
- Use parágrafos curtos
- Adicione imagens (recomendado: 1200x675px)
- Use headings (H2, H3) para organizar
- Adicione links relevantes

Imagem Destacada:
- Tamanho recomendado: 1200x675px (16:9)
- Formato: JPG ou PNG
- Peso: máximo 500KB (otimize antes!)

Categorias:
☑ Selecione pelo menos uma

Tags:
Adicione 3-5 tags relevantes

Resumo (Excerpt):
Escreva 2-3 frases resumindo a notícia
```

Clique em **"Publicar"**

---

### **2. Departamentos**

**Menu:** `Departamentos → Adicionar Novo`

```
Título: [Nome do Departamento]
Exemplo: Departamento de Ciências Sociais

Conteúdo:
- Apresentação do departamento
- Histórico
- Áreas de atuação
- Corpo docente
- Contato

Campos Personalizados:
- Sigla: DCS
- Site: https://dcs.cchla.ufrn.br
- Email: dcs@cchla.ufrn.br
- Telefone: (84) 3215-XXXX
- Coordenador: Prof. Dr. Nome do Coordenador

Imagem Destacada:
Logo ou foto do departamento (300x300px)
```

---

### **3. Cursos**

**Menu:** `Cursos → Adicionar Novo`

```
Título: [Nome do Curso]
Exemplo: Bacharelado em Ciências Sociais

Conteúdo:
- Apresentação do curso
- Objetivo
- Perfil do egresso
- Grade curricular (ou link)
- Coordenação

Campos Personalizados:
- Tipo: Graduação / Pós-Graduação
- Modalidade: Presencial / EaD
- Turno: Matutino / Vespertino / Noturno / Integral
- Duração: 8 semestres
- Vagas: 50 por ano
- Coordenador: Prof. Dr. Nome
- Email: coordenacao@exemplo.br
- Site: https://curso.exemplo.br

Imagem Destacada:
Banner do curso (1200x400px)
```

---

### **4. Publicações**

**Menu:** `Publicações → Adicionar Nova`

```
Título: [Título da publicação]

Conteúdo:
- Resumo
- Autores
- Data de publicação
- ISBN/ISSN (se aplicável)

Campos Personalizados:
- Autores: Nome 1, Nome 2, Nome 3
- Ano: 2024
- Editora: Editora Exemplo
- ISBN: 978-XX-XXXX-XXX-X
- Tipo: Livro / Artigo / Capítulo / Tese / Dissertação
- Link Externo: (se disponível online)
- Arquivo PDF: (upload do PDF)

Imagem Destacada:
Capa da publicação (600x900px)
```

---

### **5. Especiais (Vídeos/Projetos)**

**Menu:** `Especiais → Adicionar Novo`

```
Título: [Nome do especial]

Conteúdo:
- Descrição do projeto/vídeo
- Contexto
- Participantes

Campos Personalizados:
- Tipo: Vídeo / Documentário / Projeto / Evento
- URL do Vídeo: https://youtube.com/watch?v=XXXXX
- Data do Evento: 15/06/2024
- Local: Campus Central - UFRN
- Responsável: Prof. Dr. Nome

Imagem Destacada:
Thumbnail ou banner (1200x675px)
```

---

### **6. Serviços**

**Menu:** `Serviços → Adicionar Novo`

```
Título: [Nome do serviço]
Exemplo: Atendimento Psicológico à Comunidade

Conteúdo:
- Descrição do serviço
- Público-alvo
- Como solicitar
- Requisitos
- Contato

Campos Personalizados:
- Horário: Segunda a sexta, 8h às 17h
- Local: Sala XXX - CCHLA
- Responsável: Prof. Nome
- Telefone: (84) 3342-XXXX
- Email: servico@cchla.ufrn.br
- Tipo: Atendimento / Consultoria / Curso / Outro

Ícone:
Escolha um ícone FontAwesome
Exemplo: fa-hand-holding-heart
```

---

### **7. Acesso Rápido (Links Externos)**

**Menu:** `Acesso Rápido → Adicionar Novo`

```
Título: [Nome do sistema]
Exemplo: SIGAA - Sistema Acadêmico

Conteúdo:
- Breve descrição do sistema
- Para que serve
- Público

Campos Personalizados:
- URL Externa: https://sigaa.ufrn.br
- Ícone: fa-graduation-cap
- Abrir em: Nova aba / Mesma aba
- Cor de Destaque: #1B4D9E (azul CCHLA)

Imagem Destacada:
Logo do sistema (300x300px)
```

---

## 🔒 Segurança e Performance

### **1. Segurança Básica**

#### **A) Ocultar versão do WordPress**

Adicione no `functions.php`:

```php
remove_action('wp_head', 'wp_generator');
```

#### **B) Desabilitar edição de arquivos no admin**

Adicione no `wp-config.php`:

```php
define('DISALLOW_FILE_EDIT', true);
```

#### **C) Proteger wp-config.php**

No `.htaccess`:

```apache
<files wp-config.php>
order allow,deny
deny from all
</files>
```

#### **D) Limitar tentativas de login**

Instale: **Limit Login Attempts Reloaded**

Configuração recomendada:
- Máximo: 3 tentativas
- Bloqueio: 20 minutos

---

### **2. Backup**

#### **A) Backup Automático**

Instale: **UpdraftPlus WordPress Backup Plugin**

Configure:
- Frequência: Diário
- Armazenamento: Google Drive / Dropbox / FTP
- Manter: 7 backups

#### **B) Backup Manual**

```bash
# Banco de dados
mysqldump -u usuario -p cchla_ufrn > backup_$(date +%Y%m%d).sql

# Arquivos
tar -czf backup_files_$(date +%Y%m%d).tar.gz /var/www/html
```

---

### **3. Cache e Performance**

#### **A) Plugin de Cache**

Instale: **WP Super Cache**

Configurações:
- ☑ Cache ligado
- ☑ Compressão
- ☑ Não guardar cache para usuários logados

#### **B) Otimização de Imagens**

Instale: **Smush Image Compression and Optimization**

Configure:
- ☑ Compressão automática ao upload
- ☑ Lazy load
- ☑ Redimensionar imagens grandes

#### **C) CDN (Opcional)**

Para melhor performance, use:
- **Cloudflare** (grátis)
- **BunnyCDN** (pago, mas barato)

---

### **4. SSL/HTTPS**

#### **Forçar HTTPS**

No `wp-config.php`, adicione antes de `/* That's all, stop editing! */`:

```php
define('FORCE_SSL_ADMIN', true);

if (strpos($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') !== false)
    $_SERVER['HTTPS'] = 'on';
```

No `.htaccess`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Problemas Comuns

### **1. "Erro ao estabelecer conexão com banco de dados"**

**Solução:**
- Verifique credenciais no `wp-config.php`
- Teste conexão: `mysql -u usuario -p`
- Verifique se MySQL está rodando: `systemctl status mysql`


### **2. "Página não encontrada" (404) após ativação**

**Solução:**
1. Vá em: `Configurações → Links Permanentes`
2. Clique em **"Salvar Alterações"** (sem mudar nada)
3. Isso regenera o `.htaccess`


### **3. Erro "Upload: falha ao escrever o arquivo no disco"**

**Solução:**

```bash
sudo chown -R www-data:www-data /var/www/html/wp-content/uploads
sudo chmod -R 755 /var/www/html/wp-content/uploads
```


### **4. Site lento**

**Soluções:**
1. Ative plugin de cache (WP Super Cache)
2. Otimize imagens (Smush)
3. Desative plugins desnecessários
4. Use CDN (Cloudflare)
5. Aumente memória PHP:

No `wp-config.php`:
```php
define('WP_MEMORY_LIMIT', '256M');
```


### **5. "White Screen of Death"**

**Solução:**
1. Ative debug no `wp-config.php`:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

2. Verifique log: `wp-content/debug.log`
3. Geralmente causado por:
   - Plugin incompatível (desative todos via FTP)
   - Tema com erro (troque para tema padrão via banco)
   - Falta de memória (aumente `WP_MEMORY_LIMIT`)


### **6. Menu do Admin não carrega**

**Solução:**

Renomeie pasta de plugins via FTP:
```bash
mv wp-content/plugins wp-content/plugins-old
```

Crie nova pasta:
```bash
mkdir wp-content/plugins
```

Reative plugins um por um para encontrar o problema.


### **7. Imagens não aparecem após migração**

**Solução:**

Rode no banco de dados (phpMyAdmin):

```sql
UPDATE wp_options SET option_value = replace(option_value, 'http://sitiantigo.com', 'https://sitenovo.com') WHERE option_name = 'home' OR option_name = 'siteurl';

UPDATE wp_posts SET guid = replace(guid, 'http://sitiantigo.com','https://sitenovo.com');

UPDATE wp_posts SET post_content = replace(post_content, 'http://sitiantigo.com', 'https://sitenovo.com');

UPDATE wp_postmeta SET meta_value = replace(meta_value,'http://sitiantigo.com','https://sitenovo.com');
```

Ou use plugin: **Better Search Replace**


## Suporte

### **Documentação Oficial**

- WordPress: https://wordpress.org/documentation/
- WordPress Brasil: https://br.wordpress.org/
- Fórum de Suporte: https://br.forums.wordpress.org/

### **Recursos Úteis**

- **Stack Overflow:** https://stackoverflow.com/questions/tagged/wordpress
- **WordPress TV:** https://wordpress.tv/ (vídeos tutoriais)
- **WordPress Codex:** https://codex.wordpress.org/

### **Contato da Equipe de Desenvolvimento**

Para problemas específicos do tema CCHLA:
- **Email:** agenciaweb@ifrn.edu.br
- **Site:** https://agenciaweb.ifrn.edu.br



## Checklist Final

Antes de colocar o site no ar, verifique:

```
☐ WordPress instalado e atualizado
☐ Tema CCHLA ativado
☐ Plugins essenciais instalados
☐ Links permanentes configurados (Nome do post)
☐ Página inicial definida
☐ Menus criados e atribuídos
☐ Informações de contato preenchidas
☐ Redes sociais configuradas
☐ SSL/HTTPS ativo e funcionando
☐ Backup automático configurado
☐ Cache ativado
☐ Imagens otimizadas
☐ Google Analytics configurado (opcional)
☐ Política de Privacidade publicada
☐ Testado em mobile e desktop
☐ Formulário de contato testado
☐ Busca testada
☐ 404 personalizado funcionando
☐ Remover "Desencorajar mecanismos de busca"
☐ Deletar posts/páginas de exemplo
☐ Deletar temas padrão não utilizados
☐ Deletar plugins não utilizados
☐ Senha admin forte
☐ Email de recuperação válido
```



## Parabéns!

Seu site está no ar! 

Para atualizações e melhorias futuras, mantenha sempre:
- ✅ WordPress atualizado
- ✅ Plugins atualizados
- ✅ Tema atualizado
- ✅ Backups regulares
- ✅ PHP atualizado

**Bom trabalho! 🚀**



**Versão:** 1.0  
**Última Atualização:** Novembro 2025
**Desenvolvido por:** Agência Web - IFRN