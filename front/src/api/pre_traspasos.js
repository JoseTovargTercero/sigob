import {
  confirmNotification,
  hideLoader,
  showLoader,
  toastNotification,
} from "../helpers/helpers.js";
import { NOTIFICATIONS_TYPES } from "../helpers/types.js";
import { APP_URL, config } from "./urlConfig.js";

const traspasoUrl = `${APP_URL}${config.MODULE_NAMES.EJECUCION}/pre_traspasos.php`;

const getTraspasos = async (id_ejercicio) => {
  showLoader();

  try {
    let res = await fetch(traspasoUrl, {
      method: "POST",
      body: JSON.stringify({
        accion: "consultar_todos",
        id_ejercicio,
      }),
    });
    /*
     let clone = res.clone();
     let text = await clone.text();
     console.log("respuesta " + text);
    */
    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    const json = await res.json();

    console.log(json);

    if (json.success) {
      return json.success;
    }

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      });
    }
    return json;
  } catch (e) {
    console.log(e);

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: "Error al obtener traspasos",
    });
  } finally {
    hideLoader();
  }
};

const getTraspaso = async (id) => {
  showLoader();
  try {
    let res = await fetch(traspasoUrl, {
      method: "POST",
      body: JSON.stringify({
        accion: "consultar_por_id",
        id,
      }),
    });

    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    // let clone = res.clone()
    // let text = await clone.text()

    // console.log(text)

    const json = await res.json();
    console.log(json);

    if (json.success) {
      return json.success;
    }

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      });
    }
    return json;
  } catch (e) {
    console.log(e);

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: "Error al obtener traspasos",
    });
  } finally {
    hideLoader();
  }
};

const registrarTraspaso = async (informacion) => {
  showLoader();
  try {
    let res = await fetch(traspasoUrl, {
      method: "POST",
      body: JSON.stringify({
        accion: "registrar",
        ...informacion,
      }),
    });

    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    let clone = res.clone();
    let text = await clone.text();

    console.log(text);

    const json = await res.json();

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      });
    }

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      });
    }
    return json;
  } catch (e) {
    console.log(e);

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: "Error al obtener traspasos",
    });
  } finally {
    hideLoader();
  }
};

const aceptarTraspaso = async (id, codigo) => {
  showLoader();
  try {
    let res = await fetch(traspasoUrl, {
      method: "POST",
      body: JSON.stringify({
        accion: "aceptar",
        id,
        codigo,
      }),
    });

    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    // let clone = res.clone()
    // let text = await clone.text()

    // console.log(text)

    const json = await res.json();

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      });
    }

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      });
    }
    return json;
  } catch (e) {
    console.log(e);

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: "Error al obtener traspasos",
    });
  } finally {
    hideLoader();
  }
};

const rechazarTraspaso = async (id) => {
  showLoader();
  try {
    let res = await fetch(traspasoUrl, {
      method: "POST",
      body: JSON.stringify({
        accion: "rechazar",
        id,
      }),
    });

    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    let clone = res.clone();
    let text = await clone.text();

    console.log(text);

    const json = await res.json();

    if (json.success) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.done,
        message: json.success,
      });
    }

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      });
    }
    return json;
  } catch (e) {
    console.log(e);

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: "Error al obtener traspasos",
    });
  } finally {
    hideLoader();
  }
};

const ultimosTraspasos = async (id_ejercicio) => {
  console.log(id_ejercicio);

  showLoader();
  try {
    let res = await fetch(traspasoUrl, {
      method: "POST",
      body: JSON.stringify({
        accion: "ultima_orden",
        id_ejercicio,
      }),
    });

    if (!res.ok) throw { status: res.status, statusText: res.statusText };

    // let clone = res.clone()
    // let text = await clone.text()

    // console.log(text)

    const json = await res.json();

    console.log(json);

    if (json.success) {
      return json.success;
    }

    if (json.error) {
      toastNotification({
        type: NOTIFICATIONS_TYPES.fail,
        message: json.error,
      });
    }
    return json;
  } catch (e) {
    console.log(e);

    return confirmNotification({
      type: NOTIFICATIONS_TYPES.fail,
      message: "Error al obtener traspasos",
    });
  } finally {
    hideLoader();
  }
};

export {
  getTraspasos,
  getTraspaso,
  registrarTraspaso,
  aceptarTraspaso,
  rechazarTraspaso,
  ultimosTraspasos,
};
