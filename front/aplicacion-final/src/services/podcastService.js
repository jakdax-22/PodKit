import { API_URL } from "@/main"; 
import AXIOS from "@/utils/axiosInstance";
import axios from "axios";

const podcastService = {
    async saveChanges(formDataToSend) {
        try {
          const response = await AXIOS.post(`${API_URL}?action=editPodcast`, formDataToSend);
          return response;
        } catch (error) {
          throw error;
        }
      },
      async deletePodcast(id) {
        const formDataToSend = new FormData();
        formDataToSend.append('id', String(id));
      
        try {
          const response = await AXIOS.post(`${API_URL}?action=deletePodcast`, formDataToSend);
          return response;
        } catch (error) {
          throw error;
        }
      },
      async fetchPodcastDetails(podcastId) {
        try {
          const response = await axios.get(`${API_URL}?action=getPodcast&idpodcast=${podcastId}`);
          return response;
        } catch (error) {
          throw error;
        }
      },
      async uploadPodcast(formData) {
        const response = await AXIOS.post(`${API_URL}?action=uploadPodcast`, formData);
        return response;
      }
      
      
      
}

export default podcastService