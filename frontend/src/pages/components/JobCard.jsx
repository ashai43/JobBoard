import { useState } from "react";
export default function JobCard({ job, onApply }) {
  const [expanded, setExpanded] = useState(false),
    [hidden, setHidden] = useState(false);
  if (hidden) return null;
  return (
    <article className="job-card">
      <div className="job-card-top">
        <div>
          <h3>{job.title}</h3>
          <p className="muted">
            {job.company} · {job.city}
          </p>
        </div>
        <button
          className="btn btn-light btn-small"
          onClick={() => setHidden(true)}
        >
          Masquer
        </button>
      </div>
      <div className="chips">
        {job.contract && <span className="chip">{job.contract}</span>}
        {job.salary && <span className="chip">{job.salary}</span>}
      </div>
      <p className="job-summary">{job.summary}</p>
      {expanded && (
        <div className="job-details">
          <p>
            <strong>Description complète.</strong> {job.description}
          </p>
          <p className="muted">
            Temps de travail : 39 h · Publiée le {job.date}
          </p>
        </div>
      )}
      <div className="job-actions">
        <button
          className="btn btn-primary btn-small"
          onClick={() => onApply(job)}
        >
          Postuler
        </button>
        <button
          className="btn btn-light btn-small"
          onClick={() => setExpanded(!expanded)}
        >
          {expanded ? "Fermer les détails" : "Voir les détails"}
        </button>
      </div>
    </article>
  );
}
