<template>
  <div class="p-8 bg-gray-100 min-h-screen">
    <div class="mb-6">
      <h1 class="text-3xl font-bold mb-2">📁 Histórico Clínico</h1>
      <div class="bg-white rounded shadow p-4 space-y-1">
        <p><strong>Nome:</strong> {{ paciente.nome }}</p>
        <p><strong>CPF:</strong> {{ paciente.cpf }}</p>
        <p><strong>Idade:</strong> {{ calcularIdade(paciente.data_nascimento) }} anos</p>
      </div>
    </div>

    <!-- Apenas Anamneses -->
    <div v-if="registrosValidos.length > 0">
      <div
        v-for="(registro, index) in registrosValidos"
        :key="index"
        class="bg-white shadow-md rounded-lg p-6 mb-8 border border-gray-300"
      >

        <!-- Cabeçalho -->
        <div class="mb-4 border-b pb-2">
          <h2 class="text-xl font-bold mb-2">
            📅 Atendimento em {{ formatarData(registro.data_atendimento) }}
          </h2>
        </div>

       <!-- Anamnese -->
      <div v-if="registro.anamnese && temAnamnese(registro.anamnese)">
        <h3 class="text-lg font-semibold mb-2 text-blue-700">📝 Anamnese</h3>

        <p v-if="registro.anamnese.queixa_principal"><strong>Queixa Principal:</strong> {{ registro.anamnese.queixa_principal }}</p>
        <p v-if="registro.anamnese.historia_doenca"><strong>História da Doença:</strong> {{ registro.anamnese.historia_doenca }}</p>
        <p v-if="registro.anamnese.historico_progressivo"><strong>Histórico Médico Progressivo:</strong> {{ registro.anamnese.historico_progressivo }}</p>
        <p v-if="registro.anamnese.historico_familiar"><strong>Histórico Familiar:</strong> {{ registro.anamnese.historico_familiar }}</p>
        <p v-if="registro.anamnese.habitos_vida"><strong>Hábitos de Vida:</strong> {{ registro.anamnese.habitos_vida }}</p>
        <p v-if="registro.anamnese.revisao_sistemas"><strong>Revisão de Sistemas:</strong> {{ registro.anamnese.revisao_sistemas }}</p>
        <p v-if="registro.anamnese.outras_observacoes"><strong>Outras Observações:</strong> {{ registro.anamnese.outras_observacoes }}</p>
        <p v-if="registro.anamnese.resumo"><strong>Resumo:</strong> {{ registro.anamnese.resumo }}</p>
      </div>

      </div>
    </div>
    <!-- === TRIAGEM (ENFERMAGEM) === -->
    <section v-if="triagens?.length" class="bg-white rounded shadow p-4">
      <h2 class="text-lg font-semibold mb-3">Triagens (Enfermagem)</h2>

      <div v-for="(t, i) in triagens" :key="i" class="border rounded p-3 mb-3">
        <div class="text-sm text-gray-600 mb-1">
          <strong>Data:</strong> {{ t.data || '—' }}
        </div>
        <div class="text-sm">
          <strong>Triagem feita por:</strong>
          {{ t.profissional || '—' }}
          <span v-if="t.registro"> — {{ t.registro }}</span>
        </div>

        <div class="mt-2">
          <p v-if="t.pressao_arterial"><strong>Pressão arterial:</strong> {{ t.pressao_arterial }}</p>
        </div>

        <div class="mt-2 space-y-1">
          <div v-if="t.anamnese?.queixa_principal">
            <strong>Queixa principal:</strong> {{ t.anamnese.queixa_principal }}
          </div>
          <div v-if="t.anamnese?.historia_doenca">
            <strong>História da doença:</strong> {{ t.anamnese.historia_doenca }}
          </div>
          <div v-if="t.anamnese?.historico_medico">
            <strong>Histórico médico:</strong> {{ t.anamnese.historico_medico }}
          </div>
          <div v-if="t.anamnese?.historico_familiar">
            <strong>Histórico familiar:</strong> {{ t.anamnese.historico_familiar }}
          </div>
          <div v-if="t.anamnese?.habitos_vida">
            <strong>Hábitos de vida:</strong> {{ t.anamnese.habitos_vida }}
          </div>
          <div v-if="t.anamnese?.revisao_sistemas">
            <strong>Revisão de sistemas:</strong> {{ t.anamnese.revisao_sistemas }}
          </div>
          <div v-if="t.anamnese?.observacoes">
            <strong>Observações:</strong> {{ t.anamnese.observacoes }}
          </div>
        </div>
      </div>
    </section>

    <!-- === RECEITAS (Médico + Enfermagem) === -->
    <section v-if="todasReceitas.length" class="bg-white rounded shadow p-4">
      <h2 class="text-lg font-semibold mb-3">Receitas</h2>

      <ul class="space-y-2">
        <li v-for="(r, idx) in todasReceitas" :key="idx" class="border rounded p-3">
          <!-- Se veio da ENFERMAGEM (tem emissor 'enfermeiro' e posologia) -->
          <template v-if="r?.emissor === 'enfermeiro'">
            <div><strong>Medicamento:</strong> {{ r.nome || '—' }}</div>
            <div v-if="r.posologia"><strong>Posologia:</strong> {{ r.posologia }}</div>
            <div class="text-xs text-gray-500 mt-1">Origem: Enfermagem</div>
          </template>

          <!-- Caso contrário, formato do MÉDICO (exibe o que existir) -->
          <template v-else>
            <div><strong>Medicamento:</strong> {{ r.nome || '—' }} <span v-if="r.mg">- {{ r.mg }}</span></div>
            <div v-if="r.dosagem"><strong>Dosagem:</strong> {{ r.dosagem }}</div>
            <div v-if="r.intervaloHoras"><strong>Intervalo:</strong> {{ r.intervaloHoras }} hs</div>
            <div v-if="r.instrucao"><strong>Instrução:</strong> {{ r.instrucao }}</div>
            <!-- Se seu formato do médico usa 'detalhes' / 'tipo' / 'quantidade' / etc., acrescente aqui -->
          </template>
        </li>
      </ul>
    </section>


    <div v-else class="text-center text-gray-500 mt-10">
      <p class="italic">Nenhum atendimento registrado ainda.</p>
    </div>

    <div class="mt-8 flex gap-4">
      <button @click="voltar" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded">
        🔙 Voltar
      </button>
      <button @click="gerarPDF" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
        📄 Gerar PDF
      </button>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  paciente: Object,
  prontuarios: Array,
  triagens: Array
})

// devolve true se qualquer campo da anamnese tiver conteúdo
const temAnamnese = (a = {}) => {
  const campos = [
    'queixa_principal',
    'historia_doenca',
    'historico_progressivo',
    'historico_familiar',
    'habitos_vida',
    'revisao_sistemas',
    'outras_observacoes', // novo
    'resumo'              // novo
  ]
  return campos.some(k => (a[k] ?? '').toString().trim().length > 0)
}

const todasReceitas = computed(() => {
  const lista = Array.isArray(props.prontuarios) ? props.prontuarios : []
  return lista
    .flatMap(p => Array.isArray(p.receitas) ? p.receitas : [])
    .filter(Boolean)
})

const registrosValidos = computed(() =>
  (props.prontuarios || []).filter(r => r.anamnese && temAnamnese(r.anamnese))
)

const voltar = () => {
  router.get(`/medico/atendimento/${props.paciente.id}`)
}

const gerarPDF = () => {
  window.open(`/medico/prontuario/${props.paciente.id}/pdf`, '_blank')
}

const calcularIdade = (data) => {
  const nascimento = new Date(data)
  const hoje = new Date()
  let idade = hoje.getFullYear() - nascimento.getFullYear()
  const m = hoje.getMonth() - nascimento.getMonth()
  if (m < 0 || (m === 0 && hoje.getDate() < nascimento.getDate())) idade--
  return idade
}

const formatarData = (data) => {
  if (!data || typeof data !== 'string' || !data.includes('-')) return '—'
  const [ano, mes, dia] = data.split('-')
  return `${dia}/${mes}/${ano}`
}
</script>
