use nowtracking

#SELECT 'DISEASE', 'DATE', 'COUNTY_FIPS', 'NORMAVG', 'TWEETS', 'TOT_DAYS'
SELECT 'county_fips', 'tweets','county', 'state', 'pop','tdays','disease','tpm','score', 'scorenew'
UNION ALL
SELECT t.COUNTY_FIPS
	,sum(t.tcount) as tweets
	,c.COUNTY_NAME_LONG
	,s.STATE_ALPHA
	,POPESTIMATE2011
	,count(DISTINCT DATE) AS Days
	,d.DISEASE
	,(sum(t.tcount) / max(POPESTIMATE2011)) * 1000000 AS tweetspm
	,ln(1+(sum(t.tcount) / max(POPESTIMATE2011) * 1000000))*count(DISTINCT DATE)/7 AS score
        ,ln(1+(sum(t.tcount) / max(POPESTIMATE2011) * 1000000)*count(DISTINCT DATE)/7) AS scorenew
INTO OUTFILE '/tmp/disease.tsv'
FIELDS TERMINATED BY '\t' #OPTIONALLY ENCLOSED BY '"' 
LINES TERMINATED BY '\n'
FROM `AGGREGATES` t
INNER JOIN TAXONOMY tax ON tax.TAX_ID = t.TAX_ID
	AND tax.isactive = 1
INNER JOIN DISEASE d ON d.DIS_ID = tax.DIS_ID
	AND d.DISEASE = 'Influenza'
	AND d.isactive = 1
INNER JOIN POP_PLACES_COUNTY c ON c.COUNTY_FIPS = t.COUNTY_FIPS
INNER JOIN POP_PLACES_STATE s ON s.STATE_FIPS = c.STATE_FIPS
WHERE t.COUNTY_FIPS IS NOT NULL
	AND DATE >= CURDATE() - interval 7 day
#	AND DATE BETWEEN '2013-01-05' and '2013-01-11'
GROUP BY d.DISEASE
	,t.COUNTY_FIPS

