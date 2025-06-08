// Máscara de CPF: 000.000.000-00
export function mascaraCPF(value = '') {
  value = value.replace(/\D/g, '');
  value = value.replace(/(\d{3})(\d)/, '$1.$2');
  value = value.replace(/(\d{3})(\d)/, '$1.$2');
  value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  return value;
}

// Máscara de telefone: (00) 00000-0000 ou (00) 0000-0000
export function mascaraTelefone(value = '') {
  value = value.replace(/\D/g, '');
  if (value.length <= 10) {
    value = value.replace(/(\d{2})(\d)/, '($1) $2');
    value = value.replace(/(\d{4})(\d)/, '$1-$2');
  } else {
    value = value.replace(/(\d{2})(\d)/, '($1) $2');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
  }
  return value;
}

// Máscara de CEP: 00000-000
export function mascaraCEP(value = '') {
  value = value.replace(/\D/g, '');
  value = value.replace(/(\d{5})(\d{1,3})$/, '$1-$2');
  return value;
}

// Máscara de moeda (Real): R$ 1.234,56
export function mascaraMoeda(valor = '') {
  valor = valor.toString().replace(/\D/g, '');
  const numero = parseFloat(valor) / 100;
  return numero.toLocaleString('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  });
}

// Máscara de data: dd/mm/aaaa
export function mascaraData(value = '') {
  value = value.replace(/\D/g, '');
  value = value.replace(/(\d{2})(\d)/, '$1/$2');
  value = value.replace(/(\d{2})(\d)/, '$1/$2');
  value = value.replace(/(\d{4})(\d+)/, '$1');
  return value;
}
