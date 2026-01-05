<template>
  <div class="code-block bg-grey-lighten-2 pa-3 rounded my-3 position-relative">
    <v-btn
      icon
      size="small"
      class="copy-btn position-absolute top-0 right-0 mt-2 mr-2"
      @click="copyCode(code)"
    >
      <v-icon>mdi-content-copy</v-icon>
    </v-btn>
    <pre><code :class="'language-' + language">{{ code }}</code></pre>
  </div>
</template>

<script setup lang="ts">
import { defineProps } from 'vue';

const props = defineProps({
  code: {
    type: String,
    required: true,
  },
  language: {
    type: String,
    default: 'text',
  },
});

const copyCode = (code: string) => {
  navigator.clipboard.writeText(code).then(() => {
    alert('Código copiado!');
  }).catch(err => {
    console.error('Erro ao copiar: ', err);
  });
};
</script>

<style scoped>
pre {
  white-space: pre-wrap;
  word-wrap: break-word;
  padding-right: 40px; /* Espaço para o botão de copiar */
}

.code-block {
  position: relative;
}

.copy-btn {
  position: absolute;
  top: 8px;
  right: 8px;
  z-index: 1;
}

/* Estilos básicos para simular coloração de sintaxe */
.language-php .token.keyword {
  color: #007bff; /* Azul */
}
.language-php .token.string {
  color: #28a745; /* Verde */
}
.language-php .token.comment {
  color: #6c757d; /* Cinza */
}
.language-php .token.function {
  color: #dc3545; /* Vermelho */
}
.language-php .token.variable {
  color: #fd7e14; /* Laranja */
}
</style>