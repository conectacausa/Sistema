function renderRow(item) {
  const id = item.id;
  const nome = escapeHtml(item.nome_fantasia || "");
  const cnpj = escapeHtml(item.cnpj || "");
  const cidade = escapeHtml(item.cidade_nome || "");
  const uf = escapeHtml(item.estado_uf || "");
  const pais = escapeHtml(item.pais_nome || "");

  return `
    <tr data-id="${id}">
      <td>${nome}</td>
      <td>${cnpj}</td>
      <td>${cidade}</td>
      <td>${uf}</td>
      <td>${pais}</td>
      <td class="text-nowrap">
        <a href="javascript:void(0)" class="action-icon js-edit" data-id="${id}" title="Editar" aria-label="Editar">
          <i data-feather="edit"></i>
        </a>

        <a href="javascript:void(0)" class="action-icon js-del text-danger ms-2" data-id="${id}" title="Excluir" aria-label="Excluir">
          <i data-feather="trash-2"></i>
        </a>
      </td>
    </tr>
  `;
}
