import React, { useEffect, useState } from "react";
import { useLocation } from "react-router-dom";
import Navbar from "../components/Navbar/Navbar";
import "../styles/SurveyResults.css";

export default function SurveyResults() {
  const [results, setResults] = useState(null);
  const [error, setError] = useState(null);
  const location = useLocation();

  useEffect(() => {
    try {
      // Récupérer les données de l'URL
      const searchParams = new URLSearchParams(location.search);
      const encodedData = searchParams.get("data");

      if (!encodedData) {
        throw new Error("Aucune donnée trouvée dans l'URL");
      }

      // Décoder les données
      const decodedData = JSON.parse(atob(encodedData));
      setResults(decodedData);
    } catch (e) {
      console.error("Erreur lors du décodage des résultats:", e);
      setError("Impossible de charger les résultats du questionnaire");
    }
  }, [location]);

  if (error) {
    return (
      <div className="survey-container">
        <Navbar />
        <div className="survey-form error">
          <p>{error}</p>
        </div>
      </div>
    );
  }

  if (!results) {
    return (
      <div className="survey-container">
        <Navbar />
        <div className="survey-form">
          <p>Chargement des résultats...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="survey-container">
      <Navbar />
      <div className="survey-form results-form">
        <h2>Vos réponses au questionnaire</h2>
        <div className="results-info">
          <p>
            <strong>Email :</strong> {results.email}
          </p>
          <p>
            <strong>Date de soumission :</strong>{" "}
            {new Date(results.date).toLocaleString()}
          </p>
        </div>
        <div className="results-grid">
          {results.responses.map((response) => (
            <div key={response.questionNumber} className="result-card">
              <h3>
                Question {String(response.questionNumber).padStart(2, "0")}
              </h3>
              <p className="question-text">{response.question}</p>
              <p className="answer-text">{response.answer}</p>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
