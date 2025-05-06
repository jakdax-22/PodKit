import { API_URL } from "@/main"; 
import AXIOS from "@/utils/axiosInstance";
import axios from "axios";

const userService = {
  // Función para obtener usuarios
  async getUsers(userId) {
    try {
      const response = await AXIOS.get(`${API_URL}?action=fetchAllUsers&userId=${userId}`);
      return response;  // Retorna la respuesta completa de la API
    } catch (error) {
      console.error("Error al obtener usuarios:", error);
      throw error;  // Propaga el error para manejarlo donde se llame
    }
  },

  async makeAdmin(formData) {
    try {
      const response = await AXIOS.post(`${API_URL}?action=makeAdmin`, formData);
      return response;  
    } catch (error) {
      console.error("Error al hacer administrador:", error);
      throw error; 
    }
  },
  async login(email, password) {
    try {
      const response = await axios.get(`${API_URL}?action=login&email=${email}&password=${password}`);
      return response;
    } catch (error) {
      throw error;
    }
  },
  async recoverPassword(email) {
    try {
      const response = await axios.post(`${API_URL}/mailcontroller.php?action=recoverPassword`, { email });
      return response;
    } catch (error) {
      throw error;
    }
  },
  async saveProfileChanges(formData) {
    try {
      const response = await AXIOS.post(`${API_URL}?action=updateUserData`, formData);
      return response;
    } catch (error) {
      throw error;
    }
  },
  async fetchFriendRequests(selfId) {
    try {
      const response = await AXIOS.get(`${API_URL}?action=getFriendRequests&selfId=${selfId}`);
      return response;
    } catch (error) {
      throw error;
    }
  },
  async acceptFriendRequest(selfId, requestId) {
    const formData = new FormData();
    formData.append('selfId', selfId);
    formData.append('requestId', requestId);
  
    try {
      const response = await AXIOS.post(`${API_URL}?action=acceptFriendRequest`, formData);
      return response;
    } catch (error) {
      throw error;
    }
  },
  async declineFriendRequest(selfId, requestId) {
    const formData = new FormData();
    formData.append('selfId', selfId);
    formData.append('requestId', requestId);
  
    try {
      const response = await AXIOS.post(`${API_URL}?action=declineFriendRequest`, formData);
      return response;
    } catch (error) {
      throw error;
    }
  },
  async sendFriendRequest(emisorId, receptorId) {
    const formData = new FormData();
    formData.append('id_emisor', emisorId);
    formData.append('id_receptor', receptorId);
  
    try {
      const response = await AXIOS.post(`${API_URL}?action=sendFriendRequest`, formData);
      return response;
    } catch (error) {
      throw error;
    }
  },
  async fetchFriends(selfId) {
    try {
      const response = await AXIOS.get(`${API_URL}?action=getFriends&id=${selfId}`);
      return response;
    } catch (error) {
      throw error;
    }
  },
  async getUserData(selfId){
    try{
      const response = await AXIOS.get(`${API_URL}?action=getUserData&userId=${selfId}`);
      return response;
    } catch(error){
      throw error;
    }
  }
  
};

export default userService;
