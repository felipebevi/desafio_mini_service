<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mini Service Desk - Abertura de Chamados</title>

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50" x-data="miniServiceDesk()" x-cloak>
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Mini Service Desk</h1>
                    <p class="text-sm text-gray-600">Sistema de Abertura de Chamados</p>
                </div>
                <a href="{{ config('services.trello.board_url') }}" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21 0H3C1.343 0 0 1.343 0 3v18c0 1.656 1.343 3 3 3h18c1.656 0 3-1.344 3-3V3c0-1.657-1.344-3-3-3zM10.44 18.18c0 .795-.645 1.44-1.44 1.44H4.56c-.795 0-1.44-.646-1.44-1.44V4.56c0-.795.645-1.44 1.44-1.44H9c.795 0 1.44.645 1.44 1.44v13.62zm9.44-6.18c0 .795-.645 1.44-1.44 1.44H14c-.795 0-1.44-.645-1.44-1.44V4.56c0-.795.645-1.44 1.44-1.44h4.44c.795 0 1.44.645 1.44 1.44V12z"/>
                    </svg>
                    Ver Quadro no Trello
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Notificações -->
        <div x-show="notification.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed top-4 right-4 z-50 max-w-md">
            <div :class="notification.type === 'success' ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500'"
                 class="border-l-4 p-4 rounded-lg shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg x-show="notification.type === 'success'" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="notification.type === 'error'" class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p :class="notification.type === 'success' ? 'text-green-800' : 'text-red-800'"
                           class="text-sm font-medium" x-text="notification.message"></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="notification.show = false" class="inline-flex text-gray-400 hover:text-gray-500">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Formulário de Criação de Chamado -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Abrir Novo Chamado</h2>

                    <form @submit.prevent="submitTicket" enctype="multipart/form-data">
                        <!-- Tipo de Suporte -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipo de Suporte <span class="text-red-500">*</span>
                            </label>
                            <select x-model="form.tipo_suporte" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Selecione...</option>
                                <option value="Bug">Bug</option>
                                <option value="Melhoria">Melhoria</option>
                                <option value="Duvida/Operacao">Dúvida/Operação</option>
                                <option value="Acesso/Permissao">Acesso/Permissão</option>
                                <option value="Infra/DevOps">Infra/DevOps</option>
                            </select>
                            <p x-show="errors.tipo_suporte" x-text="errors.tipo_suporte" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Título -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Título do Chamado <span class="text-red-500">*</span>
                                <span class="text-gray-500 font-normal">(<span x-text="form.titulo.length"></span>/120)</span>
                            </label>
                            <input type="text" x-model="form.titulo" required maxlength="120"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Ex: Erro ao fazer login no sistema">
                            <p x-show="errors.titulo" x-text="errors.titulo" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Descrição -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Descrição Detalhada <span class="text-red-500">*</span>
                            </label>
                            <textarea x-model="form.descricao" required rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Descreva o problema ou sugestão em detalhes..."></textarea>
                            <p x-show="errors.descricao" x-text="errors.descricao" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Solicitante -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Seu Nome <span class="text-red-500">*</span>
                                </label>
                                <input type="text" x-model="form.solicitante_nome" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Nome completo">
                                <p x-show="errors.solicitante_nome" x-text="errors.solicitante_nome" class="mt-1 text-sm text-red-600"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Seu E-mail <span class="text-red-500">*</span>
                                </label>
                                <input type="email" x-model="form.solicitante_email" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="seu@email.com">
                                <p x-show="errors.solicitante_email" x-text="errors.solicitante_email" class="mt-1 text-sm text-red-600"></p>
                            </div>
                        </div>

                        <!-- Prioridade -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Prioridade <span class="text-red-500">*</span>
                            </label>
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" x-model="form.prioridade" value="Baixa" required class="mr-2">
                                    <span class="text-sm">Baixa</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" x-model="form.prioridade" value="Media" required class="mr-2">
                                    <span class="text-sm">Média</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" x-model="form.prioridade" value="Alta" required class="mr-2">
                                    <span class="text-sm">Alta</span>
                                </label>
                            </div>
                            <p x-show="errors.prioridade" x-text="errors.prioridade" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <!-- Campos Opcionais -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Sistema/Produto Afetado
                                </label>
                                <input type="text" x-model="form.sistema_afetado"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Ex: Portal, CRM, Site">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Ambiente
                                </label>
                                <select x-model="form.ambiente"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Selecione...</option>
                                    <option value="Producao">Produção</option>
                                    <option value="Homologacao">Homologação</option>
                                    <option value="Dev">Desenvolvimento</option>
                                </select>
                            </div>
                        </div>

                        <!-- Anexos -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Anexos (máx. 3 arquivos, 10MB cada)
                            </label>
                            <input type="file" @change="handleFileUpload" multiple accept=".png,.jpg,.jpeg,.pdf,.doc,.docx,.txt"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PNG, JPG, PDF, DOC, DOCX, TXT</p>
                            <p x-show="errors.anexos" x-text="errors.anexos" class="mt-1 text-sm text-red-600"></p>

                            <!-- Preview dos arquivos -->
                            <div x-show="fileNames.length > 0" class="mt-2 space-y-1">
                                <template x-for="(file, index) in fileNames" :key="index">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd"/>
                                        </svg>
                                        <span x-text="file"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Botão Submit -->
                        <div class="flex justify-end">
                            <button type="submit" :disabled="loading"
                                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-medium rounded-lg transition-colors flex items-center">
                                <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loading ? 'Enviando...' : 'Abrir Chamado'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar: Informações e Chamados -->
            <div class="space-y-6">
                <!-- Informações da API -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Informações da API</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-600 font-medium">Board do Trello:</p>
                            <a href="{{ config('services.trello.board_url') }}" target="_blank" class="text-blue-600 hover:underline break-all">
                                Ver Quadro
                            </a>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Endpoint para criar chamado:</p>
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded block mt-1">POST /api/chamados</code>
                        </div>
                        <div>
                            <p class="text-gray-600 font-medium">Endpoint para listar chamados:</p>
                            <code class="text-xs bg-gray-100 px-2 py-1 rounded block mt-1">GET /api/chamados</code>
                        </div>
                    </div>
                </div>

                <!-- Últimos Chamados -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Chamados Recentes</h3>
                        <button @click="loadTickets" :disabled="loadingTickets"
                                class="text-sm text-blue-600 hover:text-blue-700 disabled:text-gray-400">
                            <svg class="w-5 h-5" :class="{'animate-spin': loadingTickets}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                        </button>
                    </div>

                    <div x-show="Object.keys(tickets).length === 0 && !loadingTickets" class="text-center text-gray-500 py-4">
                        Nenhum chamado encontrado
                    </div>

                    <div class="space-y-3">
                        <template x-for="(cards, listName) in tickets" :key="listName">
                            <div x-show="cards.length > 0">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2" x-text="listName"></h4>
                                <div class="space-y-2">
                                    <template x-for="card in cards.slice(0, 3)" :key="card.id">
                                        <a :href="card.url" target="_blank"
                                           class="block p-3 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 transition-colors">
                                            <p class="text-sm font-medium text-gray-900" x-text="card.name"></p>
                                            <div class="flex gap-1 mt-1">
                                                <template x-for="label in card.labels.slice(0, 2)" :key="label.id">
                                                    <span class="text-xs px-2 py-1 rounded"
                                                          :style="'background-color: ' + getLabelColor(label.color) + '; color: white;'"
                                                          x-text="label.name"></span>
                                                </template>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function miniServiceDesk() {
            return {
                form: {
                    tipo_suporte: '',
                    titulo: '',
                    descricao: '',
                    solicitante_nome: '',
                    solicitante_email: '',
                    prioridade: '',
                    sistema_afetado: '',
                    ambiente: '',
                },
                errors: {},
                fileNames: [],
                loading: false,
                loadingTickets: false,
                notification: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                tickets: {},

                init() {
                    this.loadTickets();
                },

                handleFileUpload(event) {
                    const files = event.target.files;
                    this.fileNames = Array.from(files).map(file => file.name);
                },

                async submitTicket() {
                    this.loading = true;
                    this.errors = {};

                    const formData = new FormData();

                    // Adicionar campos do formulário
                    Object.keys(this.form).forEach(key => {
                        if (this.form[key]) {
                            formData.append(key, this.form[key]);
                        }
                    });

                    // Adicionar arquivos
                    const fileInput = document.querySelector('input[type="file"]');
                    if (fileInput && fileInput.files.length > 0) {
                        Array.from(fileInput.files).forEach(file => {
                            formData.append('anexos[]', file);
                        });
                    }

                    try {
                        const response = await fetch('/api/chamados', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.showNotification('Chamado criado com sucesso! Redirecionando para o Trello...', 'success');
                            this.resetForm();
                            this.loadTickets();

                            // Redirecionar para o card no Trello após 2 segundos
                            setTimeout(() => {
                                window.open(data.trello_url, '_blank');
                            }, 2000);
                        } else {
                            if (data.errors) {
                                this.errors = Object.keys(data.errors).reduce((acc, key) => {
                                    acc[key] = data.errors[key][0];
                                    return acc;
                                }, {});
                            }
                            this.showNotification(data.message || 'Erro ao criar chamado', 'error');
                        }
                    } catch (error) {
                        this.showNotification('Erro de conexão. Verifique sua internet e tente novamente.', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async loadTickets() {
                    this.loadingTickets = true;

                    try {
                        const response = await fetch('/api/chamados');
                        const data = await response.json();

                        if (response.ok) {
                            this.tickets = data.chamados;
                        }
                    } catch (error) {
                        console.error('Erro ao carregar chamados:', error);
                    } finally {
                        this.loadingTickets = false;
                    }
                },

                resetForm() {
                    this.form = {
                        tipo_suporte: '',
                        titulo: '',
                        descricao: '',
                        solicitante_nome: '',
                        solicitante_email: '',
                        prioridade: '',
                        sistema_afetado: '',
                        ambiente: '',
                    };
                    this.fileNames = [];
                    const fileInput = document.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.value = '';
                    }
                },

                showNotification(message, type = 'success') {
                    this.notification = { show: true, message, type };
                    setTimeout(() => {
                        this.notification.show = false;
                    }, 5000);
                },

                getLabelColor(color) {
                    const colors = {
                        'red': '#ef4444',
                        'orange': '#f97316',
                        'yellow': '#eab308',
                        'green': '#22c55e',
                        'blue': '#3b82f6',
                        'purple': '#a855f7',
                        'pink': '#ec4899',
                        'sky': '#0ea5e9',
                        'lime': '#84cc16',
                        'black': '#1f2937'
                    };
                    return colors[color] || '#6b7280';
                }
            }
        }
    </script>
</body>
</html>
