import apiClient from "../api/config";

export const surveyService = {
  async getAllSurveys() {
    return apiClient.get("/surveys");
  },

  async getQuestions() {
    return apiClient.get("/questions");
  },

  async createSurvey(data) {
    return apiClient.post("/surveys", data);
  },

  async submitResponses(token, responses) {
    return apiClient.post(`/surveys/${token}/responses`, responses);
  },

  async getSurvey(token) {
    return apiClient.get(`/surveys/${token}`);
  },

  async getResponses(token) {
    return apiClient.get(`/surveys/${token}/responses`);
  },

  async completeSurvey(token) {
    return apiClient.put(`/surveys/${token}/complete`);
  },
};
