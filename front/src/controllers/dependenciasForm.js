import { getCategorias } from "../api/categorias.js";
import {
  getDependencias,
  sendDependencia,
  updateDependencia,
} from "../api/dependencias.js";
import { confirmNotification, validateInput } from "../helpers/helpers.js";
import { NOTIFICATIONS_TYPES } from "../helpers/types.js";
import {
  confirmDeleteDependencia,
  loadDependenciaTable,
} from "./dependenciasTable.js";

const d = document;
let id;

let fieldList = {
  dependencia: "",
  cod_dependencia: "",
  id_categoria: "",
};

let fieldListErrors = {
  dependencia: {
    value: true,
    message: "Campo inválido",
    type: "text",
  },
  cod_dependencia: {
    value: true,
    message: "Campo inválido",
    type: "number",
  },
  id_categoria: {
    value: true,
    message: "Elija una categoria válida",
    type: "number",
  },
};

export function validateDependenciaForm() {
  let formId = "dependencia-form",
    formContainerId = "dependencia-form-container",
    btnNewId = "dependencia-nueva",
    btnSaveId = "dependencia-guardar";

  const formElement = d.getElementById(formId);
  const formContainerElement = d.getElementById(formContainerId);
  const btnNewElement = d.getElementById(btnNewId);

  formElement.addEventListener("input", (e) => {
    fieldList = validateInput({
      target: e.target,
      fieldList: fieldList,
      fieldListErrors: fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    });
  });

  formElement.addEventListener("change", (e) => {
    fieldList = validateInput({
      target: e.target,
      fieldList: fieldList,
      fieldListErrors: fieldListErrors,
      type: fieldListErrors[e.target.name].type,
    });
  });

  d.addEventListener("click", async (e) => {
    if (e.target.id === btnNewId) {
      formContainerElement.classList.toggle("hide");
      if (formContainerElement.classList.contains("hide")) {
        validateEditButtons();
        btnNewElement.textContent = "Nueva unidad";
        formElement.reset();
        // Resetear ID
        id = "";
      } else {
        btnNewElement.textContent = "Cancelar";
        let categorias = await getCategorias();
        insertOptions({ input: "categorias", data: categorias.mappedData });
      }
    }

    if (e.target.id === "btn-delete") {
      let fila = e.target.closest("tr");

      confirmDeleteDependencia({ id: e.target.dataset.id, row: fila });
    }
    if (e.target.id === "btn-edit") {
      // EDITAR DEPENDENCIA
      id = e.target.dataset.id;

      validateEditButtons();

      e.target.textContent = "Editando";
      e.target.setAttribute("disabled", true);

      let categorias = await getCategorias();
      insertOptions({ input: "categorias", data: categorias.mappedData });

      if (formContainerElement.classList.contains("hide")) {
        formContainerElement.classList.remove("hide");
        btnNewElement.textContent = "Cancelar";
      }
      let dependenciaData = await getDependencias(id);

      let { cod_dependencia, dependencia, id_categoria } =
        dependenciaData.fullInfo[0];

      let categoria = id_categoria
        ? categorias.mappedData.find(
            (categoria) => categoria.id == id_categoria
          )
        : "";
      console.log(categoria);

      console.log(categoria, categoria.name);
      formElement.dependencia.value = dependencia;
      formElement.cod_dependencia.value = cod_dependencia;
      formElement.id_categoria.value = categoria ? categoria.id : "";
    }

    // GUARDAR DEPENDENCIA

    if (e.target.id === btnSaveId) {
      fieldList = validateInput({
        target: formElement.dependencia,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.dependencia.name],
      });
      fieldList = validateInput({
        target: formElement.cod_dependencia,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.cod_dependencia.name],
      });
      fieldList = validateInput({
        target: formElement.id_categoria,
        fieldList,
        fieldListErrors,
        type: fieldListErrors[formElement.id_categoria.name],
      });
      if (Object.values(fieldListErrors).some((el) => el.value)) {
        return confirmNotification({
          type: NOTIFICATIONS_TYPES.fail,
          message: "Necesita llenar todos los campos",
        });
      }

      if (id) {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          message: `Deseas actualizar esta dependencia a: "${formElement.dependencia.value} - ${formElement.cod_dependencia.value}?`,
          successFunction: async function () {
            await updateDependencia({
              informacion: {
                id: id,
                dependencia: fieldList.dependencia,
                cod_dependencia: fieldList.cod_dependencia,
                id_categoria: fieldList.id_categoria,
              },
            });

            // RESETEAR FORMULARIO
            formContainerElement.classList.add("hide");
            btnNewElement.textContent = "Nueva unidad";
            formElement.reset();
            id = "";
            // Recargar tabla
            loadDependenciaTable();
          },
        });
      } else {
        confirmNotification({
          type: NOTIFICATIONS_TYPES.send,
          successFunction: async function () {
            // ENVIAR DEPENDENCIA
            await sendDependencia({
              informacion: {
                dependencia: formElement.dependencia.value,
                cod_dependencia: formElement.cod_dependencia.value,
                id_categoria: fieldList.id_categoria,
              },
            });

            // RESETEAR FORMULARIO
            formContainerElement.classList.add("hide");
            btnNewElement.textContent = "Nueva unidad";
            formElement.reset();

            // Recargar tabla
            loadDependenciaTable();
          },
          message: `Deseas guardar la dependencia "${formElement.dependencia.value} - ${formElement.cod_dependencia.value}
          "`,
        });
      }
    }
  });

  function insertOptions({ input, data }) {
    const selectElement = d.getElementById(`search-select-${input}`);
    selectElement.innerHTML = `<option value="">Elegir...</option>`;
    const fragment = d.createDocumentFragment();
    data.forEach((el) => {
      const option = d.createElement("option");
      option.setAttribute("value", el.id);
      option.textContent = el.name;
      fragment.appendChild(option);
    });

    selectElement.appendChild(fragment);
  }

  function validateEditButtons() {
    let editButtons = d.querySelectorAll("[data-id][disabled]");

    editButtons.forEach((btn) => {
      if (btn.hasAttribute("disabled")) {
        btn.removeAttribute("disabled");
        btn.textContent = "Editar";
      }
    });
  }

  return;
}
