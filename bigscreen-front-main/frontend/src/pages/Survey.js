import React, { useEffect, useMemo, useState, useCallback } from "react";
import Navbar from "../components/Navbar/Navbar";
import "../styles/Survey.css";
import { surveyService } from "../services/SurveyService";
import { getCsrfCookie } from "../api/config";

export default function Survey() {
  const [questions, setQuestions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  // answers keyed by question.number → { [number]: value }
  const [answers, setAnswers] = useState({});
  const [currentIndex, setCurrentIndex] = useState(0);
  const [showReview, setShowReview] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [submitResult, setSubmitResult] = useState(null); // holds API response

  // -------- Load questions from backend --------
  useEffect(() => {
    let cancelled = false;
    async function load() {
      setLoading(true);
      setError("");
      try {
        await getCsrfCookie(); // Get CSRF cookie before making requests
        const { data } = await surveyService.getQuestions();
        if (!data?.success)
          throw new Error("Réponse API invalide pour /questions");
        const qs = (data.questions || []).map((q) => ({
          id: q.id,
          number: q.number,
          text: q.content,
          type: q.type, // 'A' | 'B' | 'C'
          options: q.options || null,
          is_required: q.is_required,
        }));
        if (!cancelled) setQuestions(qs);
      } catch (e) {
        console.error(e);
        if (!cancelled)
          setError("Impossible de charger les questions. Réessayez plus tard.");
      } finally {
        if (!cancelled) setLoading(false);
      }
    }
    load();
    return () => {
      cancelled = true;
    };
  }, []);

  const currentQuestion = useMemo(
    () => questions[currentIndex],
    [questions, currentIndex]
  );

  const handleInputChange = (questionNumber, value) => {
    setAnswers((prev) => ({ ...prev, [questionNumber]: value }));
  };

  const ensureAnswered = useCallback(() => {
    if (!currentQuestion) return false;
    const val = answers[currentQuestion.number];
    if (
      currentQuestion.is_required &&
      (val === undefined || val === null || val === "")
    ) {
      alert("Veuillez fournir une réponse avant de continuer.");
      return false;
    }
    // Additional email sanity-check for Q1 if backend uses it as email
    if (currentQuestion.number === 1 && currentQuestion.type === "B") {
      const email = String(val || "").trim();
      const emailRegex = /[^@\s]+@[^@\s]+\.[^@\s]+/;
      if (!emailRegex.test(email)) {
        alert("Veuillez saisir une adresse email valide.");
        return false;
      }
    }
    return true;
  }, [currentQuestion, answers]);

  const handleNext = useCallback(() => {
    if (!ensureAnswered()) return;
    if (currentIndex < questions.length - 1) setCurrentIndex((i) => i + 1);
    else setShowReview(true);
  }, [currentIndex, questions.length, ensureAnswered]);

  const handlePrev = () => {
    if (currentIndex > 0) setCurrentIndex((i) => i - 1);
  };

  // Add keyboard event listener for Enter key
  useEffect(() => {
    const handleKeyPress = (event) => {
      if (event.key === "Enter" && !showReview && !loading) {
        event.preventDefault();
        handleNext();
      }
    };

    document.addEventListener("keypress", handleKeyPress);
    return () => {
      document.removeEventListener("keypress", handleKeyPress);
    };
  }, [handleNext, showReview, loading]);

  // -------- Submit flow: create survey then submit responses --------
  const handleSubmitAll = async () => {
    if (!questions.length) return;
    setSubmitting(true);
    setSubmitResult(null);
    try {
      // Get CSRF token before submitting
      await getCsrfCookie();

      // 1) Create survey with email from question 1 (assumption: Q1 is email)
      const emailAnswer = answers[1];
      const { data: createData } = await surveyService.createSurvey({
        email: emailAnswer,
      });

      if (!createData?.success) {
        throw new Error(
          createData?.message || "Échec de la création du survey"
        );
      }

      const token = createData.survey?.token;
      if (!token)
        throw new Error("Token absent dans la réponse de création du survey");

      // 2) Build payload for responses as { question_1: ..., question_2: ... }
      const payload = {};
      for (const q of questions) {
        const key = `question_${q.number}`;
        let val = answers[q.number];

        // Pour les questions de type C (notes), convertir en nombre
        if (q.type === "C" && val !== undefined && val !== null && val !== "") {
          val = Number(val);
        }

        // Assurer que toutes les questions ont une valeur
        // Si pas de réponse, mettre une chaîne vide
        payload[key] = val === undefined || val === null ? "" : val;
      }

      const { data: submitData } = await surveyService.submitResponses(
        token,
        payload
      );

      if (!submitData?.success) {
        throw new Error(submitData?.message || "Échec de l'envoi des réponses");
      }

      // Créer un objet avec toutes les réponses et questions
      const surveyResults = {
        responses: questions.map((q) => ({
          question: q.text,
          questionNumber: q.number,
          answer: answers[q.number] || "",
          type: q.type,
        })),
        email: emailAnswer,
        date: new Date().toISOString(),
        token: token,
      };

      // Encoder les données en base64
      const encodedData = btoa(JSON.stringify(surveyResults));

      setSubmitResult({
        ok: true,
        token,
        response_url: `/survey-results?data=${encodedData}`,
      });
    } catch (e) {
      console.error(e);
      setSubmitResult({ ok: false, error: e.message });
      alert(e.message || "Erreur lors de l'envoi du sondage");
    } finally {
      setSubmitting(false);
    }
  };

  const renderTypeA = (q) => (
    <div className="options-container">
      {(q.options || []).map((opt, i) => (
        <div
          key={i}
          className={`option ${answers[q.number] === opt ? "selected" : ""}`}
          onClick={() => handleInputChange(q.number, opt)}
          role="button"
          tabIndex={0}
          onKeyDown={(e) =>
            e.key === "Enter" ? handleInputChange(q.number, opt) : null
          }
        >
          {opt}
        </div>
      ))}
    </div>
  );

  const renderTypeB = (q) => (
    <div className="input-container">
      <input
        type={q.number === 1 ? "email" : "text"}
        maxLength={255}
        value={answers[q.number] || ""}
        onChange={(e) => handleInputChange(q.number, e.target.value)}
        placeholder={
          q.number === 1 ? "Votre adresse email" : "Votre réponse..."
        }
      />
    </div>
  );

  const renderTypeC = (q) => {
    const values =
      Array.isArray(q.options) && q.options.length
        ? q.options
        : [1, 2, 3, 4, 5];
    return (
      <div className="numeric-container">
        {values.map((num) => (
          <div
            key={num}
            className={`numeric-option ${
              Number(answers[q.number]) === Number(num) ? "selected" : ""
            }`}
            onClick={() => handleInputChange(q.number, num)}
            role="button"
            tabIndex={0}
            onKeyDown={(e) =>
              e.key === "Enter" ? handleInputChange(q.number, num) : null
            }
          >
            {num}
          </div>
        ))}
      </div>
    );
  };

  const renderQuestion = (q) => {
    if (!q) return null;
    switch (q.type) {
      case "A":
        return renderTypeA(q);
      case "B":
        return renderTypeB(q);
      case "C":
        return renderTypeC(q);
      default:
        return null;
    }
  };

  if (loading) {
    return (
      <div className="survey-container">
        <Navbar />
        <div className="survey-form">
          <p>Chargement des questions…</p>
        </div>
      </div>
    );
  }

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

  if (!questions.length) {
    return (
      <div className="survey-container">
        <Navbar />
        <div className="survey-form">
          <p>Aucune question disponible.</p>
        </div>
      </div>
    );
  }

  if (showReview) {
    if (submitResult?.ok) {
      return (
        <div className="survey-container">
          <Navbar />
          <div className="survey-form final-message">
            <div className="submit-result">
              <h3>Merci d'avoir complété le questionnaire!</h3>
              <p>Cliquez sur le lien ci-dessous pour voir vos réponses :</p>
              <div className="response-link">
                <a
                  href={submitResult.response_url}
                  className="result-link"
                  target="_blank"
                  rel="noreferrer"
                >
                  ➡️ Voir mes réponses »
                </a>
              </div>
            </div>
          </div>
        </div>
      );
    }

    return (
      <div className="survey-container">
        <Navbar />
        <div className="survey-form review-form">
          <h2>Révision des réponses</h2>
          <div className="answers-grid">
            {questions.map((q) => (
              <div key={q.number} className="answer-card">
                <h3>
                  Q{String(q.number).padStart(2, "0")} — {q.text}
                </h3>
                <p className="answer-text">
                  {String(answers[q.number] ?? "").toString()}
                </p>
              </div>
            ))}
          </div>

          <div className="review-actions">
            <button
              className="back-button"
              onClick={() => setShowReview(false)}
              disabled={submitting}
            >
              Retour
            </button>
            <button
              className="submit-button"
              onClick={handleSubmitAll}
              disabled={submitting}
            >
              {submitting ? "Envoi..." : "Terminer"}
            </button>
          </div>

          {submitResult && !submitResult.ok && (
            <div className="submit-result error">{submitResult.error}</div>
          )}
        </div>
      </div>
    );
  }

  return (
    <div className="survey-container">
      <Navbar />

      <div className="survey-form">
        <div className="question-header">
          <h2>
            Question: {String(currentIndex + 1).padStart(2, "0")} /{" "}
            {questions.length}
          </h2>
        </div>

        <div className="question-text">
          <p>{currentQuestion?.text}</p>
        </div>

        {renderQuestion(currentQuestion)}

        <div className="nav-actions">
          <button
            className="back-button"
            onClick={handlePrev}
            disabled={currentIndex === 0}
          >
            Précédent
          </button>
          <button className="submit-button" onClick={handleNext}>
            {currentIndex === questions.length - 1 ? "Réviser" : "Suivant"}
          </button>
        </div>

        <div className="progress-bar">
          {questions.map((_, idx) => (
            <div
              key={idx}
              className={`progress-dot ${
                idx === currentIndex ? "active" : ""
              } ${idx < currentIndex ? "completed" : ""}`}
            />
          ))}
        </div>
      </div>
    </div>
  );
}
