import bcrypt from 'bcryptjs';

// In-Memory Data Store for WMS Agiliza
class Store {
  constructor() {
    this.usuarios = [];
    this.produtos = [];
    this.enderecos = [];
    this.slas = [];
    this.logsSeguranca = [];
    this.userIdSeq = 1;
    this.produtoIdSeq = 1;
    this.enderecoIdSeq = 1;

    this.initSeed();
  }

  initSeed() {
    const hashGestor = bcrypt.hashSync('Gestor@123', 10);
    const hashOper = bcrypt.hashSync('Operador@123', 10);

    // 1. Usuários Padrão
    this.usuarios.push({
      id: this.userIdSeq++,
      matricula: 'ADMIN01',
      nome_completo: 'Gestor Padrão do Sistema',
      perfil: 'GESTOR',
      senha_hash: hashGestor,
      created_at: new Date('2026-08-25T10:00:00Z'),
      updated_at: null,
      deleted_at: null,
      created_by: null,
    });

    this.usuarios.push({
      id: this.userIdSeq++,
      matricula: 'OPER01',
      nome_completo: 'Operador Galpão 01',
      perfil: 'OPERADOR',
      senha_hash: hashOper,
      created_at: new Date('2026-08-25T10:05:00Z'),
      updated_at: null,
      deleted_at: null,
      created_by: 1,
    });

    // 2. Configurações de SLA Padrão
    const etapas = ['RECEBIDO', 'A_ARMAZENAR', 'A_SEPARAR', 'A_EXPEDIR'];
    for (const etapa of etapas) {
      this.slas.push({
        id: this.slas.length + 1,
        etapa_kanban: etapa,
        tempo_limite_minutos: 120,
      });
    }

    // 3. Endereços Iniciais (incluindo Quarentena Virtual)
    this.enderecos.push({
      id: this.enderecoIdSeq++,
      rua: 'QUA',
      predio: '00',
      nivel: '00',
      capacidade_maxima: 999999,
      created_at: new Date('2026-08-25T10:10:00Z'),
      updated_at: null,
      deleted_at: null,
    });

    this.enderecos.push({
      id: this.enderecoIdSeq++,
      rua: '01',
      predio: '01',
      nivel: '01',
      capacidade_maxima: 1000,
      created_at: new Date('2026-08-25T10:11:00Z'),
      updated_at: null,
      deleted_at: null,
    });

    this.enderecos.push({
      id: this.enderecoIdSeq++,
      rua: '01',
      predio: '01',
      nivel: '02',
      capacidade_maxima: 1000,
      created_at: new Date('2026-08-25T10:12:00Z'),
      updated_at: null,
      deleted_at: null,
    });

    this.enderecos.push({
      id: this.enderecoIdSeq++,
      rua: '01',
      predio: '02',
      nivel: '01',
      capacidade_maxima: 1000,
      created_at: new Date('2026-08-25T10:13:00Z'),
      updated_at: null,
      deleted_at: null,
    });

    this.enderecos.push({
      id: this.enderecoIdSeq++,
      rua: '02',
      predio: '01',
      nivel: '01',
      capacidade_maxima: 800,
      created_at: new Date('2026-08-25T10:14:00Z'),
      updated_at: null,
      deleted_at: null,
    });

    // 4. Produtos Iniciais
    this.produtos.push({
      id: this.produtoIdSeq++,
      sku: 'SKU-1001',
      codigo_barras: '7891234567890',
      descricao: 'Parafuso Sextavado ZB 1/4 x 2 pol',
      unidade_medida: 'UN',
      curva_abc: 'A',
      created_at: new Date('2026-08-25T10:20:00Z'),
      updated_at: null,
      deleted_at: null,
      created_by: 1,
    });

    this.produtos.push({
      id: this.produtoIdSeq++,
      sku: 'SKU-1002',
      codigo_barras: '7891234567891',
      descricao: 'Porca Sextavada 1/4 pol',
      unidade_medida: 'CX',
      curva_abc: 'B',
      created_at: new Date('2026-08-25T10:21:00Z'),
      updated_at: null,
      deleted_at: null,
      created_by: 1,
    });

    this.produtos.push({
      id: this.produtoIdSeq++,
      sku: 'SKU-1003',
      codigo_barras: '7891234567892',
      descricao: 'Arruela Lisa 1/4 pol',
      unidade_medida: 'PCT',
      curva_abc: 'C',
      created_at: new Date('2026-08-25T10:22:00Z'),
      updated_at: null,
      deleted_at: null,
      created_by: 1,
    });

    this.produtos.push({
      id: this.produtoIdSeq++,
      sku: 'SKU-2001',
      codigo_barras: '7891234567893',
      descricao: 'Abraçadeira de Nylon 200mm Preto',
      unidade_medida: 'PCT',
      curva_abc: 'A',
      created_at: new Date('2026-08-25T10:23:00Z'),
      updated_at: null,
      deleted_at: null,
      created_by: 1,
    });

    this.produtos.push({
      id: this.produtoIdSeq++,
      sku: 'SKU-2002',
      codigo_barras: '7891234567894',
      descricao: 'Fita Isolante 3M Imperial 20m',
      unidade_medida: 'RL',
      curva_abc: 'B',
      created_at: new Date('2026-08-25T10:24:00Z'),
      updated_at: null,
      deleted_at: null,
      created_by: 1,
    });
  }

  // --- Usuários ---
  getUsuarios(search = '') {
    const term = (search || '').trim().toLowerCase();
    return this.usuarios.filter(u => {
      if (u.deleted_at !== null) return false;
      if (!term) return true;
      return (
        u.matricula.toLowerCase().includes(term) ||
        u.nome_completo.toLowerCase().includes(term)
      );
    });
  }

  findUsuarioById(id) {
    const numId = Number(id);
    return this.usuarios.find(u => u.id === numId && u.deleted_at === null) || null;
  }

  findUsuarioByMatricula(matricula) {
    const mat = (matricula || '').trim().toUpperCase();
    return this.usuarios.find(u => u.matricula.toUpperCase() === mat && u.deleted_at === null) || null;
  }

  createUsuario({ matricula, nome_completo, perfil, senha, created_by }) {
    const senha_hash = bcrypt.hashSync(senha, 10);
    const novo = {
      id: this.userIdSeq++,
      matricula: matricula.trim().toUpperCase(),
      nome_completo: nome_completo.trim(),
      perfil: perfil === 'GESTOR' ? 'GESTOR' : 'OPERADOR',
      senha_hash,
      created_at: new Date(),
      updated_at: null,
      deleted_at: null,
      created_by: created_by || null,
    };
    this.usuarios.push(novo);
    return novo;
  }

  updateUsuario(id, { matricula, nome_completo, perfil, senha, updated_by }) {
    const usuario = this.findUsuarioById(id);
    if (!usuario) return null;

    if (matricula) usuario.matricula = matricula.trim().toUpperCase();
    if (nome_completo) usuario.nome_completo = nome_completo.trim();
    if (perfil) usuario.perfil = perfil === 'GESTOR' ? 'GESTOR' : 'OPERADOR';
    if (senha && senha.trim()) {
      usuario.senha_hash = bcrypt.hashSync(senha.trim(), 10);
    }
    usuario.updated_at = new Date();
    usuario.updated_by = updated_by || null;
    return usuario;
  }

  deleteUsuario(id) {
    const usuario = this.findUsuarioById(id);
    if (!usuario) return false;
    usuario.deleted_at = new Date();
    return true;
  }

  // --- Produtos ---
  getProdutos(search = '') {
    const term = (search || '').trim().toLowerCase();
    return this.produtos.filter(p => {
      if (p.deleted_at !== null) return false;
      if (!term) return true;
      return (
        p.sku.toLowerCase().includes(term) ||
        p.codigo_barras.toLowerCase().includes(term) ||
        p.descricao.toLowerCase().includes(term)
      );
    });
  }

  findProdutoById(id) {
    const numId = Number(id);
    return this.produtos.find(p => p.id === numId && p.deleted_at === null) || null;
  }

  findProdutoBySku(sku, excludeId = null) {
    const term = (sku || '').trim().toUpperCase();
    return this.produtos.find(p => {
      if (p.deleted_at !== null) return false;
      if (excludeId && p.id === Number(excludeId)) return false;
      return p.sku.toUpperCase() === term;
    }) || null;
  }

  findProdutoByCodigoBarras(codigo, excludeId = null) {
    const term = (codigo || '').trim();
    return this.produtos.find(p => {
      if (p.deleted_at !== null) return false;
      if (excludeId && p.id === Number(excludeId)) return false;
      return p.codigo_barras === term;
    }) || null;
  }

  createProduto({ sku, codigo_barras, descricao, unidade_medida, curva_abc, created_by }) {
    const novo = {
      id: this.produtoIdSeq++,
      sku: sku.trim().toUpperCase(),
      codigo_barras: codigo_barras.trim(),
      descricao: descricao.trim(),
      unidade_medida: (unidade_medida || 'UN').trim().toUpperCase(),
      curva_abc: ['A', 'B', 'C'].includes(curva_abc) ? curva_abc : 'C',
      created_at: new Date(),
      updated_at: null,
      deleted_at: null,
      created_by: created_by || null,
    };
    this.produtos.push(novo);
    return novo;
  }

  updateProduto(id, { sku, codigo_barras, descricao, unidade_medida, curva_abc, updated_by }) {
    const produto = this.findProdutoById(id);
    if (!produto) return null;

    if (sku) produto.sku = sku.trim().toUpperCase();
    if (codigo_barras) produto.codigo_barras = codigo_barras.trim();
    if (descricao) produto.descricao = descricao.trim();
    if (unidade_medida) produto.unidade_medida = unidade_medida.trim().toUpperCase();
    if (curva_abc && ['A', 'B', 'C'].includes(curva_abc)) produto.curva_abc = curva_abc;
    produto.updated_at = new Date();
    produto.updated_by = updated_by || null;
    return produto;
  }

  deleteProduto(id) {
    const produto = this.findProdutoById(id);
    if (!produto) return false;
    produto.deleted_at = new Date();
    return true;
  }

  // --- Endereços ---
  getEnderecos(search = '') {
    const term = (search || '').trim().toLowerCase();
    return this.enderecos.filter(e => {
      if (e.deleted_at !== null) return false;
      if (!term) return true;
      const formatted = `r${e.rua}-p${e.predio}-n${e.nivel}`.toLowerCase();
      return (
        e.rua.toLowerCase().includes(term) ||
        e.predio.toLowerCase().includes(term) ||
        e.nivel.toLowerCase().includes(term) ||
        formatted.includes(term)
      );
    });
  }

  findEnderecoById(id) {
    const numId = Number(id);
    return this.enderecos.find(e => e.id === numId && e.deleted_at === null) || null;
  }

  findEnderecoByFormat(rua, predio, nivel, excludeId = null) {
    const r = (rua || '').trim().toUpperCase();
    const p = (predio || '').trim().toUpperCase();
    const n = (nivel || '').trim().toUpperCase();

    return this.enderecos.find(e => {
      if (e.deleted_at !== null) return false;
      if (excludeId && e.id === Number(excludeId)) return false;
      return (
        e.rua.toUpperCase() === r &&
        e.predio.toUpperCase() === p &&
        e.nivel.toUpperCase() === n
      );
    }) || null;
  }

  createEndereco({ rua, predio, nivel, capacidade_maxima }) {
    const novo = {
      id: this.enderecoIdSeq++,
      rua: rua.trim().toUpperCase(),
      predio: predio.trim().toUpperCase(),
      nivel: nivel.trim().toUpperCase(),
      capacidade_maxima: Number(capacidade_maxima) || 1000,
      created_at: new Date(),
      updated_at: null,
      deleted_at: null,
    };
    this.enderecos.push(novo);
    return novo;
  }

  updateEndereco(id, { rua, predio, nivel, capacidade_maxima }) {
    const endereco = this.findEnderecoById(id);
    if (!endereco) return null;

    if (rua) endereco.rua = rua.trim().toUpperCase();
    if (predio) endereco.predio = predio.trim().toUpperCase();
    if (nivel) endereco.nivel = nivel.trim().toUpperCase();
    if (capacidade_maxima !== undefined) endereco.capacidade_maxima = Number(capacidade_maxima) || 1000;
    endereco.updated_at = new Date();
    return endereco;
  }

  deleteEndereco(id) {
    const endereco = this.findEnderecoById(id);
    if (!endereco) return false;
    endereco.deleted_at = new Date();
    return true;
  }

  // --- Logs de Segurança ---
  registrarSeguranca(tipo, detalhe) {
    this.logsSeguranca.push({
      id: this.logsSeguranca.length + 1,
      tipo,
      detalhe,
      created_at: new Date(),
    });
    console.log(`[SEGURANÇA] [${tipo}] ${detalhe}`);
  }
}

export const store = new Store();
