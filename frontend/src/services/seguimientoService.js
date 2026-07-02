import api from './api'

const seguimientoService = {
  consultar(codigo) {
    return api.get(`/ordenes/seguimiento/${encodeURIComponent(codigo)}`)
  },
}

export default seguimientoService
