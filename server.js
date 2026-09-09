import express from 'express';
import session from 'express-session';
import path from 'path';
import { fileURLToPath } from 'url';
import bcrypt from 'bcryptjs';
import { store } from './store.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = 3000;
const HOST = '0.0.0.0';

// View Engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Body parser
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Static assets
app.use('/assets', express.static(path.join(__dirname, 'assets')));
app.use('/uploads', express.static(path.join(__dirname, 'uploads')));

// Session configuration (8h timeout)
app.use(
  session({
    secret: process.env.SESSION_SECRET || 'wms_agiliza_secret_key_2026',
    resave: false,
    saveUninitialized: false,
    cookie: {
      maxAge: 8 * 60 * 60 * 1000, // 8 horas
      httpOnly: true,
    },
  })
);

// Flash messages and global variables middleware
app.use((req, res, next) => {
  res.locals.usuario = req.session.user || null;
  res.locals.flash_sucesso = req.session.flash_sucesso || null;
  res.locals.flash_erro = req.session.flash_erro || null;
  delete req.session.flash_sucesso;
  delete req.session.flash_erro;
  next();
});

// Auth Middlewares
function requireLogin(req, res, next) {
  if (!req.session.user) {
    return res.redirect('/login');
  }
  next();
}

function requirePerfil(perfilRequerido) {
  return (req, res, next) => {
    if (!req.session.user) {
      return res.redirect('/login');
    }
    if (req.session.user.perfil !== perfilRequerido) {
      store.registrarSeguranca(
        'ACESSO_NEGADO',
        `Tentativa de acesso não autorizado pelo usuário ${req.session.user.matricula} à rota ${req.originalUrl}`
      );
      return res.status(403).render('errors/403', {
        currentRoute: req.path,
        title: '403 - Acesso Negado',
      });
    }
    next();
  };
}

// ================= ROTEAMENTO =================

// Raiz
app.get('/', (req, res) => {
  if (req.session.user) {
    return res.redirect('/produtos');
  }
  res.redirect('/login');
});

// Autenticação
app.get('/login', (req, res) => {
  if (req.session.user) {
    return res.redirect('/produtos');
  }
  res.render('auth/login', { erro: null, matricula: '' });
});

app.post('/login', (req, res) => {
  const matricula = (req.body.matricula || '').trim();
  const senha = req.body.senha || '';

  const usuario = store.findUsuarioByMatricula(matricula);
  if (usuario && bcrypt.compareSync(senha, usuario.senha_hash)) {
    req.session.user = {
      id: usuario.id,
      matricula: usuario.matricula,
      nome_completo: usuario.nome_completo,
      perfil: usuario.perfil,
    };
    store.registrarSeguranca('LOGIN_SUCESSO', `Matrícula: ${matricula}`);
    return res.redirect('/produtos');
  }

  store.registrarSeguranca('LOGIN_FALHA', `Matrícula tentada: ${matricula}`);
  res.render('auth/login', {
    erro: 'Matrícula ou senha incorretos.',
    matricula,
  });
});

app.post('/logout', (req, res) => {
  const usuarioId = req.session.user ? req.session.user.id : 'desconhecido';
  store.registrarSeguranca('LOGOUT', `Usuário ID: ${usuarioId}`);
  req.session.destroy(() => {
    res.redirect('/login');
  });
});

// Rota Utilitária de Seed
app.get('/seed', (req, res) => {
  store.initSeed();
  res.send('<h1>Carga inicial de dados re-executada com sucesso!</h1><a href="/login">Ir para o Login</a>');
});

// ================= MÓDULO DE PRODUTOS =================

app.get('/produtos', requireLogin, (req, res) => {
  const q = req.query.q || '';
  const produtos = store.getProdutos(q);
  res.render('produtos/index', {
    produtos,
    q,
    currentRoute: '/produtos',
  });
});

app.get('/produtos/criar', requireLogin, (req, res) => {
  res.render('produtos/form', {
    produto: null,
    erro: null,
    formData: {},
    currentRoute: '/produtos',
  });
});

app.post('/produtos/criar', requireLogin, (req, res) => {
  const sku = (req.body.sku || '').trim();
  const codigo_barras = (req.body.codigo_barras || '').trim();
  const descricao = (req.body.descricao || '').trim();
  const unidade_medida = (req.body.unidade_medida || 'UN').trim();
  const curva_abc = (req.body.curva_abc || 'C').trim();

  if (!sku || !codigo_barras || !descricao) {
    return res.render('produtos/form', {
      produto: null,
      erro: 'Por favor, preencha todos os campos obrigatórios (SKU, Código de Barras e Descrição).',
      formData: req.body,
      currentRoute: '/produtos',
    });
  }

  if (store.findProdutoBySku(sku)) {
    return res.render('produtos/form', {
      produto: null,
      erro: `Já existe um produto ativo cadastrado com o SKU informado ('${sku}').`,
      formData: req.body,
      currentRoute: '/produtos',
    });
  }

  if (store.findProdutoByCodigoBarras(codigo_barras)) {
    return res.render('produtos/form', {
      produto: null,
      erro: `Já existe um produto ativo cadastrado com o Código de Barras informado ('${codigo_barras}').`,
      formData: req.body,
      currentRoute: '/produtos',
    });
  }

  store.createProduto({
    sku,
    codigo_barras,
    descricao,
    unidade_medida,
    curva_abc,
    created_by: req.session.user.id,
  });

  req.session.flash_sucesso = `Produto SKU '${sku}' cadastrado com sucesso!`;
  res.redirect('/produtos');
});

app.get('/produtos/editar', requireLogin, (req, res) => {
  const id = req.query.id;
  const produto = store.findProdutoById(id);

  if (!produto) {
    return res.status(404).send('<h1>404 - Produto Não Encontrado</h1>');
  }

  res.render('produtos/form', {
    produto,
    erro: null,
    formData: {},
    currentRoute: '/produtos',
  });
});

app.post('/produtos/editar', requireLogin, (req, res) => {
  const id = req.query.id;
  const produto = store.findProdutoById(id);

  if (!produto) {
    return res.status(404).send('<h1>404 - Produto Não Encontrado</h1>');
  }

  const sku = (req.body.sku || '').trim();
  const codigo_barras = (req.body.codigo_barras || '').trim();
  const descricao = (req.body.descricao || '').trim();
  const unidade_medida = (req.body.unidade_medida || 'UN').trim();
  const curva_abc = (req.body.curva_abc || 'C').trim();

  if (!sku || !codigo_barras || !descricao) {
    return res.render('produtos/form', {
      produto,
      erro: 'Por favor, preencha todos os campos obrigatórios.',
      formData: req.body,
      currentRoute: '/produtos',
    });
  }

  if (store.findProdutoBySku(sku, id)) {
    return res.render('produtos/form', {
      produto,
      erro: `O SKU '${sku}' já está sendo utilizado por outro produto.`,
      formData: req.body,
      currentRoute: '/produtos',
    });
  }

  if (store.findProdutoByCodigoBarras(codigo_barras, id)) {
    return res.render('produtos/form', {
      produto,
      erro: `O Código de Barras '${codigo_barras}' já está sendo utilizado por outro produto.`,
      formData: req.body,
      currentRoute: '/produtos',
    });
  }

  store.updateProduto(id, {
    sku,
    codigo_barras,
    descricao,
    unidade_medida,
    curva_abc,
    updated_by: req.session.user.id,
  });

  req.session.flash_sucesso = `Produto SKU '${sku}' atualizado com sucesso!`;
  res.redirect('/produtos');
});

app.post('/produtos/excluir', requireLogin, (req, res) => {
  const id = req.body.id;
  const produto = store.findProdutoById(id);
  if (produto) {
    store.deleteProduto(id);
    req.session.flash_sucesso = `Produto SKU '${produto.sku}' removido com sucesso!`;
  }
  res.redirect('/produtos');
});

// ================= MÓDULO DE ENDEREÇOS =================

app.get('/enderecos', requireLogin, (req, res) => {
  const q = req.query.q || '';
  const enderecos = store.getEnderecos(q);
  res.render('enderecos/index', {
    enderecos,
    q,
    currentRoute: '/enderecos',
  });
});

app.get('/enderecos/criar', requireLogin, (req, res) => {
  res.render('enderecos/form', {
    endereco: null,
    erro: null,
    formData: {},
    currentRoute: '/enderecos',
  });
});

app.post('/enderecos/criar', requireLogin, (req, res) => {
  const rua = (req.body.rua || '').trim();
  const predio = (req.body.predio || '').trim();
  const nivel = (req.body.nivel || '').trim();
  const capacidade_maxima = Number(req.body.capacidade_maxima) || 1000;

  if (!rua || !predio || !nivel) {
    return res.render('enderecos/form', {
      endereco: null,
      erro: 'Por favor, preencha todos os componentes do endereço (Rua, Prédio e Nível).',
      formData: req.body,
      currentRoute: '/enderecos',
    });
  }

  if (store.findEnderecoByFormat(rua, predio, nivel)) {
    return res.render('enderecos/form', {
      endereco: null,
      erro: `Já existe um endereço ativo cadastrado nesta posição ('Rua ${rua} - Prédio ${predio} - Nível ${nivel}').`,
      formData: req.body,
      currentRoute: '/enderecos',
    });
  }

  store.createEndereco({
    rua,
    predio,
    nivel,
    capacidade_maxima,
  });

  const pad = (s) => String(s).padStart(2, '0');
  const format = `R${pad(rua)}-P${pad(predio)}-N${pad(nivel)}`;
  req.session.flash_sucesso = `Endereço '${format}' cadastrado com sucesso!`;
  res.redirect('/enderecos');
});

app.get('/enderecos/editar', requireLogin, (req, res) => {
  const id = req.query.id;
  const endereco = store.findEnderecoById(id);

  if (!endereco) {
    return res.status(404).send('<h1>404 - Endereço Não Encontrado</h1>');
  }

  res.render('enderecos/form', {
    endereco,
    erro: null,
    formData: {},
    currentRoute: '/enderecos',
  });
});

app.post('/enderecos/editar', requireLogin, (req, res) => {
  const id = req.query.id;
  const endereco = store.findEnderecoById(id);

  if (!endereco) {
    return res.status(404).send('<h1>404 - Endereço Não Encontrado</h1>');
  }

  const rua = (req.body.rua || '').trim();
  const predio = (req.body.predio || '').trim();
  const nivel = (req.body.nivel || '').trim();
  const capacidade_maxima = Number(req.body.capacidade_maxima) || 1000;

  if (!rua || !predio || !nivel) {
    return res.render('enderecos/form', {
      endereco,
      erro: 'Por favor, preencha todos os componentes do endereço (Rua, Prédio e Nível).',
      formData: req.body,
      currentRoute: '/enderecos',
    });
  }

  if (store.findEnderecoByFormat(rua, predio, nivel, id)) {
    return res.render('enderecos/form', {
      endereco,
      erro: `Já existe outro endereço ativo cadastrado nesta posição ('Rua ${rua} - Prédio ${predio} - Nível ${nivel}').`,
      formData: req.body,
      currentRoute: '/enderecos',
    });
  }

  store.updateEndereco(id, {
    rua,
    predio,
    nivel,
    capacidade_maxima,
  });

  const pad = (s) => String(s).padStart(2, '0');
  const format = `R${pad(rua)}-P${pad(predio)}-N${pad(nivel)}`;
  req.session.flash_sucesso = `Endereço '${format}' atualizado com sucesso!`;
  res.redirect('/enderecos');
});

app.post('/enderecos/excluir', requireLogin, (req, res) => {
  const id = req.body.id;
  const endereco = store.findEnderecoById(id);
  if (endereco && !(endereco.rua === 'QUA' && endereco.predio === '00' && endereco.nivel === '00')) {
    store.deleteEndereco(id);
    const pad = (s) => String(s).padStart(2, '0');
    const format = `R${pad(endereco.rua)}-P${pad(endereco.predio)}-N${pad(endereco.nivel)}`;
    req.session.flash_sucesso = `Endereço '${format}' removido com sucesso!`;
  }
  res.redirect('/enderecos');
});

// ================= MÓDULO DE USUÁRIOS (GESTOR) =================

app.get('/usuarios', requirePerfil('GESTOR'), (req, res) => {
  const q = req.query.q || '';
  const usuarios = store.getUsuarios(q);
  res.render('usuarios/index', {
    usuarios,
    q,
    currentRoute: '/usuarios',
  });
});

app.get('/usuarios/criar', requirePerfil('GESTOR'), (req, res) => {
  res.render('usuarios/form', {
    usuarioAlvo: null,
    erro: null,
    formData: {},
    currentRoute: '/usuarios',
  });
});

app.post('/usuarios/criar', requirePerfil('GESTOR'), (req, res) => {
  const matricula = (req.body.matricula || '').trim();
  const nome_completo = (req.body.nome_completo || '').trim();
  const perfil = (req.body.perfil || 'OPERADOR').trim();
  const senha = req.body.senha || '';

  if (!matricula || !nome_completo || !senha) {
    return res.render('usuarios/form', {
      usuarioAlvo: null,
      erro: 'Por favor, preencha a Matrícula, Nome Completo e Senha.',
      formData: req.body,
      currentRoute: '/usuarios',
    });
  }

  if (senha.length < 6) {
    return res.render('usuarios/form', {
      usuarioAlvo: null,
      erro: 'A senha deve conter no mínimo 6 caracteres.',
      formData: req.body,
      currentRoute: '/usuarios',
    });
  }

  if (store.findUsuarioByMatricula(matricula)) {
    return res.render('usuarios/form', {
      usuarioAlvo: null,
      erro: `A matrícula '${matricula}' já está cadastrada para outro usuário.`,
      formData: req.body,
      currentRoute: '/usuarios',
    });
  }

  store.createUsuario({
    matricula,
    nome_completo,
    perfil,
    senha,
    created_by: req.session.user.id,
  });

  req.session.flash_sucesso = `Usuário '${matricula}' cadastrado com sucesso!`;
  res.redirect('/usuarios');
});

app.get('/usuarios/editar', requirePerfil('GESTOR'), (req, res) => {
  const id = req.query.id;
  const usuarioAlvo = store.findUsuarioById(id);

  if (!usuarioAlvo) {
    return res.status(404).send('<h1>404 - Usuário Não Encontrado</h1>');
  }

  res.render('usuarios/form', {
    usuarioAlvo,
    erro: null,
    formData: {},
    currentRoute: '/usuarios',
  });
});

app.post('/usuarios/editar', requirePerfil('GESTOR'), (req, res) => {
  const id = req.query.id;
  const usuarioAlvo = store.findUsuarioById(id);

  if (!usuarioAlvo) {
    return res.status(404).send('<h1>404 - Usuário Não Encontrado</h1>');
  }

  const matricula = (req.body.matricula || '').trim();
  const nome_completo = (req.body.nome_completo || '').trim();
  const perfil = (req.body.perfil || 'OPERADOR').trim();
  const senha = req.body.senha || '';

  if (!matricula || !nome_completo) {
    return res.render('usuarios/form', {
      usuarioAlvo,
      erro: 'Por favor, preencha a Matrícula e Nome Completo.',
      formData: req.body,
      currentRoute: '/usuarios',
    });
  }

  if (senha && senha.length < 6) {
    return res.render('usuarios/form', {
      usuarioAlvo,
      erro: 'A nova senha deve conter no mínimo 6 caracteres.',
      formData: req.body,
      currentRoute: '/usuarios',
    });
  }

  const existing = store.findUsuarioByMatricula(matricula);
  if (existing && existing.id !== Number(id)) {
    return res.render('usuarios/form', {
      usuarioAlvo,
      erro: `A matrícula '${matricula}' já está sendo utilizada por outro usuário.`,
      formData: req.body,
      currentRoute: '/usuarios',
    });
  }

  store.updateUsuario(id, {
    matricula,
    nome_completo,
    perfil,
    senha,
    updated_by: req.session.user.id,
  });

  // Se editou o próprio usuário, atualizar a sessão
  if (Number(id) === Number(req.session.user.id)) {
    req.session.user.matricula = matricula.toUpperCase();
    req.session.user.nome_completo = nome_completo;
    req.session.user.perfil = perfil;
  }

  req.session.flash_sucesso = `Usuário '${matricula}' atualizado com sucesso!`;
  res.redirect('/usuarios');
});

app.post('/usuarios/excluir', requirePerfil('GESTOR'), (req, res) => {
  const id = req.body.id;
  const usuarioAlvo = store.findUsuarioById(id);

  if (usuarioAlvo && Number(usuarioAlvo.id) !== Number(req.session.user.id)) {
    store.deleteUsuario(id);
    req.session.flash_sucesso = `Usuário '${usuarioAlvo.matricula}' desativado com sucesso!`;
  }
  res.redirect('/usuarios');
});

// 404 Handler
app.use((req, res) => {
  res.status(404).send('<h1>404 - Página não encontrada</h1>');
});

// Start Server
app.listen(PORT, HOST, () => {
  console.log(`[WMS Agiliza] Servidor iniciado em http://${HOST}:${PORT}`);
});
