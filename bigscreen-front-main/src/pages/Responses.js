import React, { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useAdminAuth } from "../contexts/AdminAuthContext";
import { surveyService } from "../services/SurveyService";
import "./Responses.css";

function Responses() {
  const navigate = useNavigate();
  const location = useLocation();
  const { isAuthenticated } = useAdminAuth();

  const [surveys, setSurveys] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [selectedSurvey, setSelectedSurvey] = useState(null);
  const [surveyResponses, setSurveyResponses] = useState(null);
  const [loadingResponses, setLoadingResponses] = useState(false);

  const handleLogout = () => {
    navigate("/login");
  };

  useEffect(() => {
    if (!isAuthenticated()) {
      navigate("/login");
      return;
    }
  }, [isAuthenticated, navigate]);

  useEffect(() => {
    const loadSurveys = async () => {
      if (!isAuthenticated()) return;

      try {
        setLoading(true);
        const response = await surveyService.getAllSurveys();

        if (response.data.success) {
          setSurveys(response.data.surveys);
        } else {
          throw new Error(response.data.message || "Erreur lors du chargement");
        }
      } catch (error) {
        console.error("Erreur chargement surveys:", error);
        setError(
          error.response?.data?.message ||
            "Erreur lors du chargement des questionnaires"
        );
      } finally {
        setLoading(false);
      }
    };

    loadSurveys();
  }, [isAuthenticated]);

  const loadSurveyResponses = async (surveyToken) => {
    try {
      setLoadingResponses(true);
      const response = await surveyService.getResponses(surveyToken);

      if (response.data.success) {
        setSurveyResponses(response.data);
        setSelectedSurvey(surveyToken);
      } else {
        throw new Error(
          response.data.message || "Erreur lors du chargement des réponses"
        );
      }
    } catch (error) {
      console.error("Erreur chargement réponses:", error);
      setError(
        error.response?.data?.message ||
          "Erreur lors du chargement des réponses"
      );
    } finally {
      setLoadingResponses(false);
    }
  };

  const renderSidebar = () => (
    <div className="sidebar">
      <div className="sidebar-header">
        <h2>BigScreen</h2>
      </div>
      <div className="sidebar-menu">
        <div
          className={`menu-item ${
            location.pathname === "/admin" ? "active" : ""
          }`}
          onClick={() => navigate("/admin")}
        >
          <span>Accueil</span>
        </div>
        <div
          className={`menu-item ${
            location.pathname === "/survey" ? "active" : ""
          }`}
          onClick={() => navigate("/survey")}
        >
          <span>Questionnaire</span>
        </div>
        <div
          className={`menu-item ${
            location.pathname === "/responses" ? "active" : ""
          }`}
          onClick={() => navigate("/responses")}
        >
          <span>Réponses</span>
        </div>
      </div>
      <div className="sidebar-footer">
        <div className="menu-item" onClick={handleLogout}>
          <span>Déconnexion</span>
        </div>
      </div>
    </div>
  );

  if (loading) {
    return (
      <div className="dashboard-container">
        {renderSidebar()}
        <div className="responses-container">
          <div>Chargement...</div>
        </div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="dashboard-container">
        {renderSidebar()}
        <div className="responses-container">
          <div className="error-message">{error}</div>
        </div>
      </div>
    );
  }

  if (selectedSurvey && surveyResponses) {
    return (
      <div className="dashboard-container">
        {renderSidebar()}
        <div className="responses-container">
          <button
            className="back-button"
            onClick={() => {
              setSelectedSurvey(null);
              setSurveyResponses(null);
            }}
          >
            ← Retour à la liste
          </button>
          <h2 className="survey-title">Réponses du questionnaire</h2>
          <div className="response-card">
            <div className="response-info">
              <p>
                <strong>Email:</strong> {surveyResponses.survey.email}
              </p>
              <p>
                <strong>Statut:</strong>{" "}
                <span
                  className={
                    surveyResponses.survey.is_completed
                      ? "status-completed"
                      : "status-pending"
                  }
                >
                  {surveyResponses.survey.is_completed
                    ? "Terminé"
                    : "À terminer"}
                </span>
              </p>
              <p>
                <strong>Date de création:</strong>{" "}
                {new Date(
                  surveyResponses.survey.created_at
                ).toLocaleDateString()}
              </p>
            </div>
          </div>
          <div className="responses-grid">
            {surveyResponses.responses.map((response) => (
              <div key={response.question_number} className="response-item">
                <div className="question-label">
                  Q{response.question_number}: {response.question_content}
                </div>
                <div className="response-value">{response.answer}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="dashboard-container">
      {renderSidebar()}
      <div className="responses-container">
        <h2 className="survey-title">
          Questionnaires soumis ({surveys.length})
        </h2>
        <div className="response-list">
          {surveys.map((survey) => (
            <div key={survey.token} className="response-card">
              <div className="response-info">
                <span>
                  <strong>Email:</strong> {survey.email}
                </span>
                <span>
                  <strong>Statut:</strong>{" "}
                  <span
                    className={
                      survey.is_completed
                        ? "status-completed"
                        : "status-pending"
                    }
                  >
                    {survey.is_completed ? "Terminé" : "À terminer"}
                  </span>
                </span>
                <span>
                  <strong>Créé le:</strong>{" "}
                  {new Date(survey.created_at).toLocaleDateString()}
                </span>
              </div>
              <div className="response-actions">
                <button
                  className="view-button"
                  onClick={() => loadSurveyResponses(survey.token)}
                  disabled={loadingResponses}
                >
                  {loadingResponses ? "Chargement..." : "Voir les réponses"}
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}

export default Responses;
