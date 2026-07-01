import api from './api'; 

const tallerService = {
  obtenerOrdenes() {
    return api.get('/ordenes');
  },

  obtenerMotocicletas() {
    return api.get('/motocicletas');
  },

  obtenerMecanicos() {
    return api.get('/mecanicos');
  },

  crearOrden(payload) {
    return api.post('/ordenes', payload); 
  },
  
  // Guarda la verificacion tecnica de la orden en TListasVerificacion.
  guardarChecklist(payload) {
    return api.post('/ordenes/guardar-verificacion', payload);
  },

  obtenerChecklist(idOrden) {
    return api.get(`/ordenes/${idOrden}/verificacion`);
  },

  // 🚀 NUEVO MÉTODO: Actualizar datos de la orden mediante PUT
  actualizarOrden(id, payload) {
    return api.put(`/ordenes/${id}`, payload);
  },

  // 🚀 NUEVO MÉTODO: Eliminar orden (baja lógica) mediante DELETE
  eliminarOrden(id) {
    return api.delete(`/ordenes/${id}`);
  },

  // 🚀 AGREGADO: Método para cambiar el estado directamente en la base de datos
  cambiarEstadoOrden(id, estado) {
    return api.put(`/ordenes/${id}/cambiar-estado`, { estado });
  },
  obtenerServicios() {
    return api.get('/servicios');
  },

  crearServicio(payload) {
    return api.post('/servicios', payload);
  },

  guardarServicioOrden(idOrden, payload) {
    return api.post(`/ordenes/${idOrden}/servicios`, payload);
  }
};

export default tallerService;
