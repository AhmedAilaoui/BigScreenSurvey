/**
 * @fileoverview Composant Responses - Gestion et affichage des réponses aux questionnaires
 * Ce composant permet aux administrateurs de visualiser l'ensemble des questionnaires
 * soumis ainsi que leurs réponses détaillées.
 */

import React, { useState, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useAdminAuth } from "../contexts/AdminAuthContext";
import { surveyService } from "../services/SurveyService";
import "./Responses.css";

/**
 * Composant Responses
 * Gère l'affichage et l'interaction avec la liste des questionnaires et leurs réponses
 * @component
 */
export default function Responses() {
  // Hooks de navigation et d'authentification
  const navigate = useNavigate();
  const location = useLocation();
  const { isAuthenticated } = useAdminAuth();

  // États pour la gestion des données
  /** @state {Array} surveys - Liste de tous les questionnaires soumis */
  const [surveys, setSurveys] = useState([]);
  /** @state {boolean} loading - État de chargement initial des questionnaires */
  const [loading, setLoading] = useState(true);
  /** @state {string|null} error - Message d'erreur éventuel */
  const [error, setError] = useState(null);
  /** @state {string|null} selectedSurvey - Token du questionnaire sélectionné */
  const [selectedSurvey, setSelectedSurvey] = useState(null);
  /** @state {Object|null} surveyResponses - Réponses du questionnaire sélectionné */
  const [surveyResponses, setSurveyResponses] = useState(null);
  /** @state {boolean} loadingResponses - État de chargement des réponses */
  const [loadingResponses, setLoadingResponses] = useState(false);

  /**
   * Gère la déconnexion de l'utilisateur
   * Redirige vers la page de connexion
   */
  const handleLogout = () => {
    navigate("/login");
  };

  /**
   * Effect Hook: Vérifie l'authentification
   * Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
   */
  useEffect(() => {
    if (!isAuthenticated()) {
      navigate("/login");
      return;
    }
  }, [isAuthenticated, navigate]);

  /**
   * Effect Hook: Charge la liste des questionnaires
   * Récupère tous les questionnaires depuis l'API au chargement du composant
   */
  useEffect(() => {
    const loadSurveys = async () => {
      if (!isAuthenticated()) return;

      try {
        setLoading(true);
        // Appel à l'API pour récupérer tous les questionnaires
        const response = await surveyService.getAllSurveys();

        // Vérifie si la requête a réussi
        if (response.data.success) {
          setSurveys(response.data.surveys);
        } else {
          throw new Error(response.data.message || "Erreur lors du chargement");
        }
      } catch (error) {
        // Gestion des erreurs avec message utilisateur approprié
        console.error("Erreur chargement surveys:", error);
        setError(
          error.response?.data?.message ||
            "Erreur lors du chargement des questionnaires"
        );
      } finally {
        // Fin du chargement dans tous les cas
        setLoading(false);
      }
    };

    // Déclenche le chargement des questionnaires
    loadSurveys();
  }, [isAuthenticated]);

  /**
   * Charge les réponses d'un questionnaire spécifique
   * @param {string} surveyToken - Token unique identifiant le questionnaire
   */
  const loadSurveyResponses = async (surveyToken) => {
    try {
      setLoadingResponses(true);
      // Récupération des réponses pour le questionnaire sélectionné
      const response = await surveyService.getResponses(surveyToken);

      if (response.data.success) {
        // Mise à jour des états avec les réponses reçues
        setSurveyResponses(response.data);
        setSelectedSurvey(surveyToken);
      } else {
        throw new Error(
          response.data.message || "Erreur lors du chargement des réponses"
        );
      }
    } catch (error) {
      // Gestion des erreurs avec message utilisateur approprié
      console.error("Erreur chargement réponses:", error);
      setError(
        error.response?.data?.message ||
          "Erreur lors du chargement des réponses"
      );
    } finally {
      // Fin du chargement des réponses
      setLoadingResponses(false);
    }
  };

  /**
   * Rendu de la barre latérale de navigation
   * @returns {JSX.Element} Barre latérale avec menu de navigation
   */
  const renderSidebar = () => (
    <div className="sidebar">
      {/* En-tête de la barre latérale */}
      <div className="sidebar-header">
        <h2>BigScreen</h2>
      </div>
      {/* Menu de navigation */}
      <div className="sidebar-menu">
        {/* Lien vers la page d'accueil */}
        <div
          className={`menu-item ${
            location.pathname === "/admin" ? "active" : ""
          }`}
          onClick={() => navigate("/admin")}
        >
          <span>Accueil</span>
        </div>
        {/* Lien vers la page du questionnaire */}
        <div
          className={`menu-item ${
            location.pathname === "/survey" ? "active" : ""
          }`}
          onClick={() => navigate("/survey")}
        >
          <span>Questionnaire</span>
        </div>
        {/* Lien vers la page des réponses */}
        <div
          className={`menu-item ${
            location.pathname === "/responses" ? "active" : ""
          }`}
          onClick={() => navigate("/responses")}
        >
          <span>Réponses</span>
        </div>
      </div>
      {/* Pied de la barre latérale avec bouton de déconnexion */}
      <div className="sidebar-footer">
        <div className="menu-item" onClick={handleLogout}>
          <span>Déconnexion</span>
        </div>
      </div>
    </div>
  );

  /**
   * Affichage pendant le chargement des données
   * @returns {JSX.Element} Écran de chargement
   */
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

  /**
   * Affichage en cas d'erreur
   * @returns {JSX.Element} Message d'erreur
   */
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

  /**
   * Affichage détaillé d'un questionnaire sélectionné
   * @returns {JSX.Element} Vue détaillée des réponses d'un questionnaire
   */
  if (selectedSurvey && surveyResponses) {
    return (
      <div className="dashboard-container">
        {renderSidebar()}
        <div className="responses-container">
          {/* Bouton de retour à la liste des questionnaires */}
          <button
            className="back-button"
            onClick={() => {
              setSelectedSurvey(null);
              setSurveyResponses(null);
            }}
          >
            ← Retour à la liste
          </button>
          {/* Titre de la section */}
          <h2 className="survey-title">Réponses du questionnaire</h2>

          {/* Carte d'information du questionnaire */}
          <div className="response-card">
            <div className="response-info">
              {/* Email du participant */}
              <p>
                <strong>Email:</strong> {surveyResponses.survey.email}
              </p>
              {/* Statut du questionnaire */}
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
              {/* Date de création du questionnaire */}
              <p>
                <strong>Date de création:</strong>{" "}
                {new Date(
                  surveyResponses.survey.created_at
                ).toLocaleDateString()}
              </p>
            </div>
          </div>

          {/* Grille des réponses */}
          <div className="responses-grid">
            {/* Mapping de chaque réponse */}
            {surveyResponses.responses.map((response) => (
              <div key={response.question_number} className="response-item">
                {/* En-tête de la question */}
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

  /**
   * Affichage par défaut : liste de tous les questionnaires
   * @returns {JSX.Element} Vue d'ensemble des questionnaires
   */
  return (
    <div className="dashboard-container">
      {/* Affichage de la barre latérale */}
      {renderSidebar()}
      
      <div className="responses-container">
        {/* En-tête avec le nombre total de questionnaires */}
        <h2 className="survey-title">
          Questionnaires soumis ({surveys.length})
        </h2>

        {/* Liste des questionnaires */}
        <div className="response-list">
          {surveys.map((survey) => (
            <div key={survey.token} className="response-card">
              {/* Informations du questionnaire */}
              <div className="response-info">
                {/* Email du participant */}
                <span>
                  <strong>Email:</strong> {survey.email}
                </span>
                {/* Statut du questionnaire */}
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
                {/* Date de création */}
                <span>
                  <strong>Créé le:</strong>{" "}
                  {new Date(survey.created_at).toLocaleDateString()}
                </span>
              </div>
              {/* Actions disponibles pour ce questionnaire */}
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
};
