// src/pages/SurveyTable.js
import React, { useEffect, useState } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useAdminAuth } from "../contexts/AdminAuthContext";
import { surveyService } from "../services/SurveyService";
import "./SurveyTable.css";

function SurveyTable() {
  const navigate = useNavigate();
  const location = useLocation();
  const { isAuthenticated, logout } = useAdminAuth();

  const [questions, setQuestions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const handleLogout = async () => {
    try {
      await logout();
    } finally {
      navigate("/login");
    }
  };

  // Rediriger si non authentifié
  useEffect(() => {
    if (!isAuthenticated()) {
      navigate("/login");
    }
  }, [isAuthenticated, navigate]);

  // Charger les questions depuis l'API
  useEffect(() => {
    if (!isAuthenticated()) return;

    let cancelled = false;

    const loadQuestions = async () => {
      try {
        setLoading(true);
        const response = await surveyService.getQuestions(); // doit appeler GET /api/questions

        if (!response?.data?.success) {
          throw new Error(
            response?.data?.message || "Erreur lors du chargement"
          );
        }

        const raw = Array.isArray(response.data.questions)
          ? response.data.questions
          : [];

        // Normaliser -> { numero, question, type }
        const normalized = raw
          .map((q, idx) => ({
            numero: q.number ?? q.id ?? idx + 1,
            question: q.content ?? "",
            type: q.type ?? "",
          }))
          .sort((a, b) => (a.numero || 0) - (b.numero || 0));

        if (!cancelled) setQuestions(normalized);
      } catch (err) {
        console.error("Erreur chargement questions:", err);
        if (!cancelled) {
          setError(
            err.response?.data?.message ||
              "Erreur lors du chargement des questions"
          );
        }
      } finally {
        if (!cancelled) setLoading(false);
      }
    };

    loadQuestions();
    return () => {
      cancelled = true;
    };
  }, [isAuthenticated]);

  if (loading) return <div className="survey-table__state">Chargement…</div>;
  if (error) return <div className="survey-table__error">{error}</div>;

  return (
    <div className="dashboard-container">
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

      <div className="survey-table-container">
        <h1>Questionnaire</h1>

        <div className="table-wrapper">
          {questions.length === 0 ? (
            <div className="survey-table__state">Aucune question trouvée.</div>
          ) : (
            <table className="survey-table">
              <thead>
                <tr>
                  <th>Numéro</th>
                  <th>Question</th>
                  <th>Type</th>
                </tr>
              </thead>
              <tbody>
                {questions.map((q) => (
                  <tr key={q.numero}>
                    <td>{q.numero}</td>
                    <td>{q.question}</td>
                    <td>{q.type}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>
    </div>
  );
}

export default SurveyTable;
